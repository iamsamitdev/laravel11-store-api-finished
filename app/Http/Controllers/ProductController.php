<?php

namespace App\Http\Controllers;

use App\Models\Product;
// use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

/**
* @OA\Schema(
*   schema="Product",
*   title="Product",
*   required={"name", "slug", "price", "category_id"},
*   @OA\Property(
*         property="id",
*         type="integer",
*         format="int64",
*         description="The unique identifier for a product",
*   ),
*   @OA\Property(
*       property="name",
*       type="string",
*       example="Product 1",
*       description="Name of product"
*   ),
*   @OA\Property(
*       property="slug",
*       type="string",
*       example="product-1",
*       description="Slug of product"
*   ),
*   @OA\Property(
*       property="price",
*       type="number",
*       example="100.00",
*       description="Price of product"
*   ),
*   @OA\Property(
*       property="category_id",
*       type="integer",
*       example="1",
*       description="Category ID of product"
*   ),
*   @OA\Property(
*       property="description",
*       type="string",
*       example="Description of product",
*       description="Description of product"
*   ),
*   @OA\Property(
*       property="user_id",
*       type="integer",
*       example="1",
*       description="User ID of product"
*   ),
*   @OA\Property(
*       property="image",
*       type="string",
*       example="product.jpg",
*       description="Image of product"
*   ),
* )
*/

class ProductController extends Controller
{

     /**
     * @OA\Get(
     *   path="/api/products",
     *   summary="Get all products",
     *   tags={"Products"},
     *   operationId="getProducts",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *       name="page",
     *       in="query",
     *       description="Page number",
     *       required=false,
     *       @OA\Schema(
     *           type="integer",
     *           default=1
     *       )
     *   ),
     *   @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       description="Number of products per page",
     *       required=false,
     *       @OA\Schema(
     *           type="integer",
     *           default=100
     *       )
     *   ),
     *   @OA\Parameter(
     *       name="searchQuery",
     *       in="query",
     *       description="Search query for product name",
     *       required=false,
     *       @OA\Schema(
     *           type="string"
     *       )
     *   ),
     *   @OA\Parameter(
     *       name="selectedCategory",
     *       in="query",
     *       description="Filter products by category",
     *       required=false,
     *       @OA\Schema(
     *           type="integer"
     *       )
     *   ),
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
     *      response=200,
     *      description="List all products",
     *      @OA\JsonContent(
     *          @OA\Property(property="Total", type="integer"),
     *          @OA\Property(
     *              property="Products",
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="slug", type="string"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="price", type="number"),
     *                  @OA\Property(property="image", type="string"),
     *                  @OA\Property(property="created_at", type="string", format="date-time"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time"),
     *                  @OA\Property(property="category_id", type="integer"),
     *                  @OA\Property(property="category_name", type="string"),
     *                  @OA\Property(property="user_fullname", type="string")
     *              )
     *          )
     *      )
     *   ),
     *   @OA\Response(
     *     response=500,
     *    description="Internal server error"
     *   ),
     * )
     */

    // อ่านรายการสินค้าทั้งหมด
    public function index(Request $request)
    {
        // Check Authentication
        $user = $request->user();

        if (!$user) {
            return response([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Read all products
        // $products = Product::all(); // SELECT * FROM products

        // Read request parameters
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 100);
        $searchQuery = $request->query('searchQuery', null);
        $selectedCategory = $request->query('selectedCategory', null);

        // Calculate skip value
        $skip = ($page - 1) * $limit;

        // Query with joins
        $query = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->join('users', 'products.user_id', '=', 'users.id')
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.description',
                'products.price',
                'products.image',
                'products.category_id',
                'products.user_id',
                'products.created_at',
                'products.updated_at',
                'categories.name as category_name',
                'users.fullname as user_fullname'
            );

        // Apply search query if it is not null or empty
        if (!empty($searchQuery)) {
            $query->where('products.name', 'ILIKE', "%{$searchQuery}%");
        }

        // Apply category filter if it is not null
        if (!empty($selectedCategory)) {
            $query->where('products.category_id', '=', $selectedCategory);
        }

        // Count total records after filtering
        $totalRecords = $query->count();

        // Get paginated results
        $products = $query->orderByDesc('products.id')
            ->skip($skip)
            ->take($limit)
            ->get();

        return response([
            'status' => true,
            'total' => $totalRecords,
            'products' => $products
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/products",
     *   summary="Create a new product",
     *   tags={"Products"},
     *   operationId="createProduct",
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
    *   @OA\RequestBody(
    *      required=true,
    *      @OA\MediaType(
    *          mediaType="multipart/form-data",
    *          @OA\Schema(
    *              required={"name","slug","price","category_id"},
    *              @OA\Property(property="name", type="string", example="Product 1", description="Name of product"),
    *              @OA\Property(property="slug", type="string", example="product-1", description="Slug of product"),
    *              @OA\Property(property="price", type="number", example="100.00", description="Price of product"),
    *              @OA\Property(property="category_id", type="integer", example="1", description="Category ID of product"),
    *              @OA\Property(property="description", type="string", example="Description of product", description="Description of product"),
    *              @OA\Property(
    *                  property="image",
    *                  type="string",
    *                  format="binary",
    *                  description="Image of the product"
    *              )
    *          ),
    *      ),
    *   ),
     *   @OA\Response(
     *      response=201,
     *      description="Product created successfully"
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
     *   ),
     *   @OA\Header(
     *     header="Accept",
     *     required=true,
     *     @OA\Schema(
     *       type="string",
     *       enum={"application/json"},
     *       default="application/json"
     *     )
     *   )
     * )
     */

    public function store(Request $request)
    {
        // Check Authentication
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        if ($user->tokenCan("1")) {

            // Validate field
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'slug' => 'required',
                'price' => 'required',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'category_id' => $request->input('category_id'),
                'user_id' => $user->id
            );

            // ตรวจสอบว่ามีการอัพโหลดไฟล์รูปภาพหรือไม่
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '.' . $image->getClientOriginalExtension();
                $uploadFolder = public_path('uploads');

                // ตรวจสอบว่าโฟลเดอร์ uploads มีหรือไม่
                if (!File::exists($uploadFolder)) {
                    File::makeDirectory($uploadFolder, 0755, true);
                }

                $image->move($uploadFolder, $fileName);
                $data_product['image'] = $fileName;
            } else {
                $data_product['image'] = 'noimg.jpg';
            }

            // Create data to tabale product
            $product = Product::create($data_product); // INSERT INTO products

            $response = [
                'status' => true,
                'message' => "Product created successfully",
                'product' => $product,
            ];

            return response($response, 201);

        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 403);
        }
    }

    /**
     * @OA\Get(
     * path="/api/products/{id}",
     * summary="Get product by id",
     * tags={"Products"},
     * operationId="getProductById",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="ID of product",
     *     @OA\Schema(
     *      type="integer",
     *      format="int64"
     *    )
     * ),
     * @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     * ),
     * @OA\Response(
     *   response=200,
     *   description="Product found"
     * ),
     * @OA\Response(
     *  response=404,
     *  description="Product not found"
     * )
     * )
     */

    public function show(Request $request, $id)
    {
        // Check Authentication
        $user = $request->user();

        if (!$user) {
            return response([
                'message' => 'Unauthorized'
            ], 401);
        }

        $product = Product::find($id); // SELECT * FROM products WHERE id = $id

        if ($product) {
            return response([
                'status' => true,
                'product' => $product
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * @OA\POST(
     *  path="/api/products/{id}",
     *  summary="Update product by id",
     *  tags={"Products"},
     *  operationId="updateProduct",
     *  security={{"bearerAuth":{}}},
     *  @OA\Parameter(
     *      name="id",
     *      in="path",
     *      required=true,
     *      description="ID of product",
     *      @OA\Schema(
     *          type="integer",
     *          format="int64"
     *      )
     *  ),
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"_method", "name", "slug", "price", "category_id"},
     *              @OA\Property(property="_method", type="string", example="PUT", description="HTTP method override"),
     *              @OA\Property(property="name", type="string", example="Product 1", description="Name of product"),
     *              @OA\Property(property="slug", type="string", example="product-1", description="Slug of product"),
     *              @OA\Property(property="price", type="number", example="100.00", description="Price of product"),
     *              @OA\Property(property="category_id", type="integer", example=1, description="Category ID"), 
     *              @OA\Property(property="description", type="string", example="Description of product", description="Description of product"),
     *              @OA\Property(
     *                  property="image",
     *                  type="string",
     *                  format="binary",
     *                  description="Image of the product"
     *              ),
     *          )
     *      ),
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="Product updated successfully",
     *      @OA\JsonContent(
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="message", type="string", example="Product updated successfully"),
     *          @OA\Property(property="product", ref="#/components/schemas/Product")
     *      )
     *  ),
     *  @OA\Response(
     *      response=401,
     *      description="Unauthorized",
     *      @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="Unauthorized")
     *      )
     *  ),
     *  @OA\Response(
     *      response=422,
     *      description="Validation Error",
     *      @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The given data was invalid."),
     *          @OA\Property(
     *              property="errors",
     *              type="object",
     *              @OA\Property(
     *                  property="field_name",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string",
     *                      example="The field_name field is required."
     *                  )
     *              )
     *          )
     *      )
     *  )
     * )
     */

    public function update(Request $request, $id)
    {
        // Check Authentication
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        if ($user->tokenCan("1")) {

            // Validate field
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3',
                'slug' => 'required',
                'price' => 'required',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'category_id' => $request->input('category_id'),
                'user_id' => $user->id
            );

            $product = Product::find($id);
            
            if (!$product) {
                return response()->json([
                    'message' => 'Product not found'
                ], 404);
            }

            // ตรวจสอบว่ามีการอัพโหลดไฟล์รูปภาพหรือไม่
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = time() . '.' . $image->getClientOriginalExtension();
                $uploadFolder = public_path('uploads');

                // ตรวจสอบว่าโฟลเดอร์ uploads มีหรือไม่
                if (!File::exists($uploadFolder)) {
                    File::makeDirectory($uploadFolder, 0755, true);
                }

                // ลบรูปภาพเก่า (ถ้ามี)
                if ($product->image && $product->image != 'noimg.jpg' && $product->image != null) {
                    $oldImagePath = $uploadFolder . '/' . $product->image;
                    if (File::exists($oldImagePath)) {
                        File::delete($oldImagePath);
                    }
                }

                // บันทึกรูปภาพใหม่
                $image->move($uploadFolder, $fileName);
                $data_product['image'] = $fileName;
            }

            $product->update($data_product); // UPDATE products SET name = $name, description = $description, slug = $slug, price = $price WHERE id = $id

            return response([
                'status' => true,
                'message' => 'Product updated successfully',
                'product' => $product
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
     *  path="/api/products/{id}",
     * summary="Delete product by id",
     * tags={"Products"},
     * operationId="deleteProduct",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *     name="id",
     *    in="path",
     *   required=true,
     * description="ID of product",
     * @OA\Schema(
     *    type="integer",
     *  format="int64"
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="Product deleted successfully"
     * ),
     * @OA\Response(
     *  response=401,
     * description="Unauthenticated"
     * )
     * )
     */

    public function destroy(Request $request, $id)
    {

        // Check Authentication
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        if ($user->tokenCan("1")) {

            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            // ตรวจสอบว่ามีการอัพโหลดไฟล์รูปภาพหรือไม่
            if ($product->image && $product->image != 'noimg.jpg' && $product->image != null) {
                $uploadFolder = public_path('uploads');
                $imagePath = $uploadFolder . '/' . $product->image;

                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }

            $product->delete(); // DELETE FROM products WHERE id = $id

            return response()->json([
                'status' => true,
                'message' => 'Product deleted successfully'
            ], 200);

        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 401);
        }

    }
}