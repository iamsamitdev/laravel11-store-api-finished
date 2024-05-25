<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;


/**
* @OA\Schema(
*   schema="Product",
*   title="Product",
*   required={"name", "slug", "price"},
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
*       property="description",
*       type="string",
*       example="Description of product",
*       description="Description of product"
*   )
* )
*/

class ProductController extends Controller
{

    /**
     * @OA\Get(
     *   path="/api/products",
     *   summary="Get all products",
     *   tags={"Products"},
     * security={{ "sanctum": {} }},
     *    @OA\Response(
     *       response=200,
     *      description="List all products"
     *   ),
     *   @OA\Response(
     *      response=401,
     *    description="Unauthenticated"
     *  )
     * )
     */

    // อ่านรายการสินค้าทั้งหมด
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Read all products
        $products = Product::all(); // SELECT * FROM products

        return response([
            'status' => true,
            'products' => $products
        ], 200);
    }


    /**
     * @OA\Post(
     *   path="/api/products",
     *   summary="Create a new product",
     *   tags={"Products"},
     * security={{ "sanctum": {} }},
     *   @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          required={"name","slug","price"},
     *          @OA\Property(property="name", type="string", example="Product 1", description="Name of product"),
     *          @OA\Property(property="slug", type="string", example="product-1", description="Slug of product"),
     *          @OA\Property(property="price", type="number", example="100.00", description="Price of product"),
     *          @OA\Property(property="description", type="string", example="Description of product", description="Description of product")
     *      ),
     *   ),
     *   @OA\Response(
     *      response=201,
     *      description="Product created successfully"
     *   ),
     *   @OA\Response(
     *      response=401,
     *      description="Unauthenticated"
     *   )
     * )
     */

    public function store(Request $request)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {

            // Validate form
            $request->validate([
                'name' => 'required|min:3',
                'slug' => 'required',
                'price' => 'required'
            ]);

            // กำหนดตัวแปรรับค่าจากฟอร์ม
            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'user_id' => $user->id
            );

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
            ], 401);
        }
    }

    /**
     * @OA\Get(
     *  path="/api/products/{id}",
     * summary="Get product by id",
     * tags={"Products"},
     * security={{ "sanctum": {} }},
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
     * description="Product found"
     * ),
     * @OA\Response(
     *  response=404,
     * description="Product not found"
     * )
     * )
     */

    public function show($id)
    {
        $product = Product::find($id); // SELECT * FROM products WHERE id = $id

        if ($product) {
            return response([
                'status' => true,
                'product' => $product
            ]);
        } else {
            return response([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *  path="/api/products/{id}",
     * summary="Update product by id",
     * tags={"Products"},
     * security={{ "sanctum": {} }},
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
     * @OA\RequestBody(
     *   required=true,
     * @OA\JsonContent(
     *      required={"name","slug","price"},
     *      @OA\Property(property="name", type="string", example="Product 1", description="Name of product"),
     *      @OA\Property(property="slug", type="string", example="product-1", description="Slug of product"),
     *      @OA\Property(property="price", type="number", example="100.00", description="Price of product"),
     *      @OA\Property(property="description", type="string", example="Description of product", description="Description of product")
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

    public function update(Request $request, $id)
    {
        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {

            $request->validate([
                'name' => 'required',
                'slug' => 'required',
                'price' => 'required'
            ]);

            $data_product = array(
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'price' => $request->input('price'),
                'user_id' => $user->id
            );

            $product = Product::find($id);
            $product->update($data_product); // UPDATE products SET name = $name, description = $description, slug = $slug, price = $price WHERE id = $id

            return response([
                'status' => true,
                'message' => 'Product updated successfully',
                'product' => $product
            ]);

        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 401);
        }
    }

    /**
     * @OA\Delete(
     *  path="/api/products/{id}",
     * summary="Delete product by id",
     * tags={"Products"},
     * security={{ "sanctum": {} }},
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

    public function destroy($id)
    {

        // เช็คสิทธิ์ (role) ว่าเป็น admin (1) 
        $user = auth()->user();

        if ($user->tokenCan("1")) {

            $product = Product::destroy($id); // DELETE FROM products WHERE id = $id

            if ($product) {
                return response([
                    'status' => true,
                    'message' => 'Product deleted successfully'
                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

        } else {
            return response([
                'status' => false,
                'message' => 'Permission denied to create'
            ], 401);
        }

    }
}