<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'firstname'  => 'required|string',
            'lastname'  => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'email'     => $request->email,
            'password'  => Hash::make($request->password)
        ]);

        $token = $user->createToken('M2mwMYQ91JKNfw610oK53ze5uFJ8LocsInqzZL')->plainTextToken;

        $response = [
            'user'  => User::findOrFail($user->id),
            'token' => $token
        ];

        return response($response,201);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email',$request->email)->first();

        // Check password
        if(!$user || !Hash::check($request->password, $user->password)){
            return response([
                'message' => 'Bad creds'
            ],401);
        }

        $token = $user->createToken('M2mwMYQ91JKNfw5uFJ8LocsInqzZL')->plainTextToken;

        $response = [
            'user'  => $user,
            'token' => $token
        ];

        return response($response,201);
    }


    public function logout(Request $request){
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Logged Out'
        ]);
    }
}
