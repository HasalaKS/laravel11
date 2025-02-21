<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid email or password.',
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => Auth::user(),
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);

        } catch (\Exception $e) {
            // ✅ Handle unexpected errors
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage() // Debugging, remove in production
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if (!$token = Auth::login($user)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User registered, but token generation failed',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 201);

        } catch (\Exception $e) {
            // ✅ Handle unexpected errors
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}
