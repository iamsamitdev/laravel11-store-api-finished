<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     required={"fullname", "username", "email", "password", "tel", "role"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="The user ID"
 *     ),
 *     @OA\Property(
 *         property="fullname",
 *         type="string",
 *         description="The user's fullname"
 *     ),
 *     @OA\Property(
 *        property="username",
 *       type="string",
 *      description="The user's username"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="The user's email address"
 *     ),
 *     @OA\Property(
 *      property="password",
 *      type="string",
 *      format="password",
 *      description="The user's password"
 *     ),
 *     @OA\Property(
 *        property="tel",
 *        type="string",
 *       description="The user's tel"
 *     ),
 *     @OA\Property(
 *      property="avatar",
 *      type="string",
 *      description="The user's avatar"
 *     ),
 *     @OA\Property(
 *      property="role",
 *      type="integer",
 *      description="The user's role"
 *     ),
 *   )
 * )
 */

class AuthController extends Controller
{
    // Register
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Registrate",
     *     operationId="Register",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="fullname",
     *                     description="Enter your fullname",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     description="Enter your username",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="Enter your Email",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="Enter your password",
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string",
     *                     format="password",
     *                     description="Enter your password confirmation",
     *                 ),
     *                 @OA\Property(
     *                     property="tel",
     *                     type="string",
     *                     description="Enter your tel",
     *                 ),
     *                 @OA\Property(
     *                     property="role",
     *                     type="string",
     *                     description="Enter your role",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Register Successfully",
     *     ),
     *     @OA\Response(
     *          response=409,
     *          description="User with this email already exists",
     *     ),
     *     @OA\Response(
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

    // Register User
    public function register(Request $request)
    {

        // Validate field
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'tel' => 'required|string',
            'role' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $fields = $validator->validated();

        // Create user
        $user = User::create([
            'fullname' => $fields['fullname'],
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'tel' => $fields['tel'],
            'role' => $fields['role']
        ]);

        $response = [
            'status' => true,
            'message' => "User registered successfully",
            'user' => $user,
        ];

        return response($response, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     operationId="Login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     description="Enter your email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     format="password",
     *                     description="Enter your password",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Login Successfully",
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Login failed",
     *     ),
     *     @OA\Response(
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

    // Login User
    public function login(Request $request)
    {

        // Validate field
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $fields = $validator->validated();

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'status' => false,
                'message' => 'Login failed'
            ], 401);
        } else {

            // ลบ token เก่าออกแล้วค่อยสร้างใหม่
            $user->tokens()->delete();

            // Create token
            $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;

            $response = [
                'status' => true,
                'message' => 'Login successfully',
                'user' => $user,
                'token' => $token
            ];

            return response($response, 201);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/refreshtoken",
     *     tags={"Auth"},
     *     summary="Refresh Token",
     *     operationId="RefreshToken",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Token refreshed",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Token refreshed"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object"
     *             ),
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 example="token_value"
     *             )
     *         )
     *     ),
     * )
     */

    // Refresh Token
    public function refreshToken(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // ลบ token เก่าออกแล้วค่อยสร้างใหม่
        $user->tokens()->delete();

        // Create token
        $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;
        $response = [
            'status' => true,
            'message' => 'Token refreshed',
            'user' => $user,
            'token' => $token
        ];
        return response($response, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     operationId="Logout",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Logged out"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *        response=500,
     *        description="An error occurred while logging out.",
     *        @OA\JsonContent(
     *        @OA\Property(
     *          property="status",
     *          type="boolean",
     *          example=false
     *        ),
     *        @OA\Property(
     *          property="message",
     *          type="string",
     *          example="An error occurred while logging out."
     *       ),
     *        @OA\Property(
     *         property="error",
     *         type="string",
     *         example="Error message"
     *     ),
     *    )
     *   )
     * )
     */
    
    // Logout User
    public function logout(Request $request)
    {

        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Delete old tokens
            // auth()->user()->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logged out'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while logging out.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
