<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            // Validasi input
            $validator = Validator::make($credentials, [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid credentials',
                    'message' => 'Email or password is incorrect'
                ], 401);
            }

            $user = auth()->user();

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => 'Could not create token: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function adminLogin(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            // Validasi input
            $validator = Validator::make($credentials, [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid credentials',
                    'message' => 'Email or password is incorrect'
                ], 401);
            }

            $user = auth()->user();

            // Check if user is admin
            if (!$user->isAdmin()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Admin access required'
                ], 403);
            }

            return response()->json([
                'message' => 'Admin login successful',
                'user' => $user,
                'token' => $token
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => 'Could not create token: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Login failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Logout failed',
                'message' => 'Token invalid or expired'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Logout failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function adminLogout()
    {
        try {
            $user = auth()->user();

            if (!$user || !$user->isAdmin()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Admin access required'
                ], 403);
            }

            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'message' => 'Admin logged out successfully'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Logout failed',
                'message' => 'Token invalid or expired'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Logout failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'User not authenticated'
                ], 401);
            }

            return response()->json([
                'user' => $user
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Token invalid or expired'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get user profile',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
