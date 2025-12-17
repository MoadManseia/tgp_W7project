<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $inputs = $request->validate([
            'username'=>['required','string','min:4','max:250'],
            'password'=>['required','min:8']
        ]);




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
