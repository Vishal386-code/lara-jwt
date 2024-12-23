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
      // Update a user’s profile
    /** 
     * @OA\Put(
     *     path="/api/update/{id}",
     *     summary="Update user profile",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "email"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="User’s name"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="User’s email"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="User’s password"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User profile updated successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|string|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
        ]);


        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    // Destroy a user
    /** 
     * @OA\Delete(
     *     path="/api/destroy/{id}",
     *     summary="Delete a user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of user to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }


}
