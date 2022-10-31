<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Mail\ContactMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        if($request->user()->role === 'SUPER'){
            return UserResource::collection(User::with('company')->where('role','ENTREPRISE')->get());
        }
        return UserResource::collection(User::all());
    }

    public function getUsers(Request $request){
        return UserResource::collection(User::with('company')
            ->where('role','USER')
            ->where('company_id',$request->user()->company_id)
            ->where('id','!=',$request->user()->id)
            ->get()
        );
    }

    public function updateUserInfo(Request $request){
        $request->validate([
            'firstname'  => 'required|string|min:3|max:200',
            'lastname'  => 'required|string|min:3|max:200',
            'email' => 'required|email',
        ]);

        $request->user()->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
        ]);

        $user = User::findOrFail($request->user()->id);

        return response($user,201);
    }

    public function updateUserPicture(Request $request){

        $request->validate([
            'image' => 'required|mimes:png,jpg,jpeg,PNG,JPG,JPEG,jfif,JFIF|max:4000'
        ]);

        $filename = time() . '.' . $request->image->extension();
        $path = $request->image->storeAs('img/users', $filename, 'public');

        $request->user()->update([
            'photo' => $path
        ]);

        return response([
            'message' => 'Your profile picture has been successfully updated ! ',
            'path'    => $path
        ],201);
    }

    public function toggleActiveUserCompany(User $user){
        $user->update([
            'active' => !$user->active
        ]);

        $res = $user->active ? 'activated':'deactivated';

        return response([
            'message' => "The company has been {$res}"
        ],201);
    }

    public function toggleActiveUser(User $user){
        $user->update([
            'active' => !$user->active
        ]);

        $res = $user->active ? 'activated':'deactivated';

        return response([
            'message' => "The user has been {$res}"
        ],201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        $request->validate([
            'firstname' => 'required|string|min:2|max:250',
            'lastname' => 'required|string|min:2|max:250',
            'email' => 'required|string|min:2|max:250|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        $active = $request->active ? true : false; 

        User::create([
            'firstname'  => $request->firstname,
            'lastname'   => $request->lastname,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'company_id' => $request->user()->company_id,
            'active'     => $active,
            'role'       => 'USER'
        ]);

        return response([
            'message' => 'Your user has been successfully created !'
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    public function deleteUserCompany(User $user){
        $user->delete();

        return response([
            'message' => "company '{$user->company->name}' has been successfully deleted!"
        ],201);
    }


    public function contact(Request $request){
        Mail::to('tiomelafranck724@gmail.com')
            ->send(new ContactMail([
                'email' => $request->email,
                'name' => $request->name,
                'tel' => $request->tel,
                'content' => $request->content,
            ]));

        return response([
            'message' => 'votre a été envoyer avec succès !'
        ],201);
    }


}
