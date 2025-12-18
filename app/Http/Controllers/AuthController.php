<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'username' => ['required', 'string', 'min:4', 'max:250'],
        'password' => ['required', 'string', 'min:6'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }


    $inputs = $validator->validated();




        $user = User::where('username',$inputs['username'])->first();

        if(!$user || !Hash::check($inputs['password'],$user->password)){
            return response()->json([
                'message'=>'wrong username or password'
            ],401);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'access_token'=>$token,
            'type'=>'Bearer'
        ]);

    }
}
