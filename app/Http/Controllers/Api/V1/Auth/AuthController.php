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
            'firstname'  => 'required|string|min:3|max:200',
            'lastname'  => 'required|string|min:3|max:200',
            'email' => 'required|email|unique:users,email|min:3|max:200',
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
                'message' => 'Incorrect identifier'
            ],401);
        }

        $token = $user->createToken('M2mwMYQ91JKfw5M2mwMYQM2mwMYQ91JKfw5uFocsInqzZL91JKfw5uFJ8LocsInqzZLuFJcsInqzZL')->plainTextToken;

        $response = [
            'user'  => $user,
            'token' => $token
        ];

        return response($response,201);
    }

    public function updateUserPassword(Request $request){
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string',
            'confirm_password' => 'required|string'
        ]);


        if(!Hash::check($request->old_password,$request->user()->password)){
            return "Incorrect password";
        }
        
        if(trim($request->new_password) !== trim($request->confirm_password)){
            return "The passwords do not match";
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response([
            'message' => 'Your password has been successfully updated'
        ],201);
    }


    public function logout(Request $request){
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Logged Out'
        ]);
    }

    public function getUserInfo(Request $request){
        return $request->user();
    }
}
