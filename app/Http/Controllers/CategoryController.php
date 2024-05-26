<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *   schema="Category",
 *   title="Category",
 *   required={"name"},
 *   @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="The category ID",
 *   ),
 *   @OA\Property(property="name", type="string", example="Category Name"),
 *   @OA\Property(property="status", type="string", example="true"),
 * )
 */

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/categories",
     *   summary="Get all categories",
     *   tags={"Categories"},
     *   operationId="getCategories",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *    ),
     *    @OA\Response(
     *       response=200,
     *      description="List all categories",
     *   ),
     *   @OA\Response(
     *     response=500,
     *    description="Internal server error"
     *   ),
     * )
     */

    public function index()
    {
        // Check Authentication
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Get all categories
        $categories = Category::all();

        return response([
            'status' => true,
            'categories' => $categories
        ], 200);
    }

    
    /**
     * @OA\Post(
     *   path="/api/categories",
     *   summary="Create a new category",
     *   tags={"Categories"},
     *   operationId="createCategory",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"name"},
     *          @OA\Property(property="name", type="string", example="Category 1", description="Name of category"),
     *          @OA\Property(property="status", type="string", example="true", description="Status of category"),
     *      ),
     *   ),
     *   @OA\Response(
     *      response=201,
     *      description="Category created successfully"
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *     response=403,
     *     description="Permission denied to create"
     *   ),
     *   @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="The given data was invalid."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="field_name",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="The field_name field is required."
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * )
     */

    public function store(Request $request)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        if ($user->tokenCan("1")) {
            // ถ้าเป็น admin ให้ทำการเพิ่มข้อมูล
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'status' => $request->status
            ]);

            return response([
                'status' => true,
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);
        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 403);
        }
    }

    /**
     * @OA\Get(
     *  path="/api/categories/{id}",
     * summary="Get categories by id",
     * tags={"Categories"},
     * operationId="getCategoryById",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *     name="id",
     *    in="path",
     *   required=true,
     * description="ID of category",
     * @OA\Schema(
     *    type="integer",
     *  format="int64"
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="Category found"
     * ),
     * @OA\Response(
     *  response=404,
     * description="Category not found"
     * )
     * )
     */

    public function show(Category $category)
    {
        $category = Category::find($category->id);

        if ($category) {
            return response([
                'status' => true,
                'category' => $category
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'Category not found'
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *  path="/api/categories/{id}",
     * summary="Update categories by id",
     * tags={"Categories"},
     * operationId="updateCategoryById",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *     name="id",
     *    in="path",
     *   required=true,
     * description="ID of category",
     * @OA\Schema(
     *    type="integer",
     *  format="int64"
     * )
     * ),
     * @OA\RequestBody(
     *   required=true,
     * @OA\JsonContent(
     *      required={"name"},
     *          @OA\Property(property="name", type="string", example="Category 1", description="Name of category"),
     *          @OA\Property(property="status", type="string", example="true", description="Status of category"),
     *   ),
     * ),
     * @OA\Response(
     *   response=200,
     * description="Product updated successfully"
     * ),
     * @OA\Response(
     *  response=401,
     * description="Unauthenticated"
     * )
     * )
     */

    public function update(Request $request, Category $category)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {
            // ถ้าเป็น admin ให้ทำการแก้ไขข้อมูล
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category->update([
                'name' => $request->name,
                'status' => $request->status
            ]);

            return response([
                'status' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to update'
            ], 403);
        }
    }

    
    /**
     * @OA\Delete(
     *  path="/api/categories/{id}",
     * summary="Delete categories by id",
     * tags={"Categories"},
     * operationId="deleteCategoryById",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *     name="id",
     *    in="path",
     *   required=true,
     * description="ID of category",
     * @OA\Schema(
     *    type="integer",
     *  format="int64"
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="Category deleted successfully"
     * ),
     * @OA\Response(
     *  response=401,
     * description="Unauthenticated"
     * )
     * )
     */

    public function destroy(Category $category)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {
            // ถ้าเป็น admin ให้ทำการลบข้อมูล
            $category->delete();

            return response([
                'status' => true,
                'message' => 'Category deleted successfully'
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to delete'
            ], 403);
        }
    }
}
