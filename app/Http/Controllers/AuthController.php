<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller; 


class AuthController extends Controller
{
    // Register a new user

    /**

    * @OA\Post(

    * path="/api/register",

    * summary="Register a new user",

    * @OA\Parameter(

    * name="name",

    * in="query",

    * description="User’s name",

    * required=true,

    * @OA\Schema(type="string")

    * ),

    * @OA\Parameter(

    * name="email",

    * in="query",

    * description="User’s email",

    * required=true,

    * @OA\Schema(type="string")

    * ),

    * @OA\Parameter(

    * name="password",

    * in="query",

    * description="User’s password",

    * required=true,

    * @OA\Schema(type="string")

    * ),

    * @OA\Response(response="201", description="User registered successfully"),

    * @OA\Response(response="422", description="Validation errors")

    * )

    */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'User created successfully!'], 201);
    }

    // Login and get JWT token


    /**

    * @OA\Post(

    * path="/api/login",

    * summary="Authenticate user and generate JWT token",

    * @OA\Parameter(

    * name="email",

    * in="query",

    * description="User’s email",

    * required=true,

    * @OA\Schema(type="string")

    * ),

    * @OA\Parameter(

    * name="password",

    * in="query",

    * description="User’s password",

    * required=true,

    * @OA\Schema(type="string")

    * ),

    * @OA\Response(response="200", description="Login successful"),

    * @OA\Response(response="401", description="Invalid credentials")

    * )

    */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json(['token' => $token]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    // Get the authenticated user

/**
 * @OA\Get(
 *     path="/api/me",
 *     summary="Get user details",
 *     @OA\Parameter(
 *         name="token",
 *         in="query",
 *         description="Bearer token",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success"
 *     )
 * )
 */
public function me() {
    return response()->json(auth()->user());
}

}
