<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    //signUp user
    //firstname, lastname, email, password
    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
        ]);
    
        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json($validator->errors(), 400);
        }
    
        try {
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
    
            Log::info('User created successfully', ['user' => $user->toArray()]);
    
            return response()->json([
                'status' => 'success', // 'status' => 'error
                'code' => 201,
                'message' => 'User created successfully',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('User creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'User creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //signIn user
    //email, password
    public function signIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json($validator->errors(), 400);
        }

        try {
            $user = User::where('email', $request->email)->first();

            if ($user && password_verify($request->password, $user->password)) {
                $token = $user->createToken('authToken')->plainTextToken;

                Log::info('User signed in successfully', ['user' => $user->toArray()]);
                return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'User signed in successfully',
                    'user' => $user,
                    'token' => $token
                ], 200);
            } else {
                Log::error('Invalid credentials');
                return response()->json([
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error('User sign in failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'User sign in failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //signOut user
    public function signOut(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            Log::info('User signed out successfully');
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'User signed out successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('User sign out failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'User sign out failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //create product
    //name, userId, description, price, image
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json($validator->errors(), 400);
        }

        try {
            $image = $request->file('image');
            $imageName = str_replace(' ', '_', $image->getClientOriginalName());
            $imagePath = $image->storeAs('products', $imageName);
        
            $product = Product::create([
                'name' => $request->name,
                'userId' => auth::id(),
                'description' => $request->description,
                'price' => $request->price,
                'image' => $imageName
            ]);

            Log::info('Product created successfully', ['product' => $product->toArray()]);
            return response()->json([
                'status' => 'success',
                'code' => 201,
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //get all products against
    public function index()
    {
        try {
            $products = Product::all();
            Log::info('Products fetched successfully', ['products' => $products->toArray()]);
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Products fetched successfully',
                'products' => $products
            ], 200);
        } catch (\Exception $e) {
            Log::error('Products fetch failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Products fetch failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //delete product
    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            $product->delete();
            Log::info('Product deleted successfully', ['product' => $product->toArray()]);
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Product deleted successfully',
                'product' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'code' => 500,
                'message' => 'Product deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
