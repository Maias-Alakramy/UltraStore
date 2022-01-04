<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        try{
            $input = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|unique:users,email',
                'password' => 'required|string|min:8',
                'c_password' => 'required|same:password',
                'contact_number' => 'required|string',
            ]);
        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage()],400);
        }
        $input['password'] = bcrypt($input['password']);
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'contact_number' => $input['contact_number']
        ]);

        $token = $user->createToken('MyApp')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function login(Request $request){
        try{
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);
        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage()],400);
        }
    
        $user = User::where('email', $request->email)->first();
    
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message'=>'The provided credentials are incorrect.'],400);
        }
    
        $token = $user->createToken('MyApp')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile()
    {
        $user = auth()->user();
        $products = auth()->user()->product;
        return response($user, 200);
    }
}