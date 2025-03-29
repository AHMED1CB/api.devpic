<?php

namespace App\Http\Controllers;

use App\Services\Response;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;



class AuthController extends Controller 
{

    function profile(){

        $user = request()->user;
        
        return $user
                ->whereId($user->id)
                ->withCount([
                    'pics',
                    'followers',
                    'followings'
                ])
                ->with([
                    'followers' => fn($q) =>  $q->select(['username' , 'bio' , 'follows.id' , 'photo', 'follows.id' ]),
                    'followings' => fn($q) => $q->select(['username' , 'bio' , 'follows.id' , 'photo' , 'follows.id']),
                    'pics' => fn($q) => $q->select('id' , 'descreption' , 'path'),
                ])
                ->first();
    }
    function register() {

        $validate = Validator::make(request()->only('username' , 'email' , 'password' , 'password_confirmation') , [
            'username' => ['required','min:3' , 'max:255' , 'unique:users' ,  'regex:/^(?=[a-zA-Z0-9._]{3,30}$)(?!.*[_.]{2})[^_.].*[^_.]$/'],
            'email' => ['required','max:255' , 'email' , 'unique:users'],
            'password' => ['required','min:8' , 'max:255' , 'confirmed']  
        ], [
            'password.min' => 'Password Must Be at Least 8 letters',
            'email.email' => 'Invalid Email',
            'email.unique' => 'Invalid Or Used Email',
            'username.unique' => 'Invalid Or Used Username',
            'username.regex' => 'Invalid Username Pattern'

        ]);


        if ($validate->fails()){
            return Response::push([
                'errors' => $validate->errors()
            ] , 'Invalid Register Data' , 400);
        
        }

        // Creating User

        $data = request()->only('username' , 'email' );
        $data['password'] = bcrypt(request()->password);
        $data['slug'] = '@'.\Illuminate\Support\Str::slug(request()->input('username'));

        // User::create($data);

        // To More Speed
        DB::table('users')->insert($data);


        return Response::push(message:'User Register Success' ,status: 201);
    

    }


    public function login(){

        $validate = Validator::make(request()->only('username' , 'password') , [
            'username' => ['required' , 'exists:users' ,  'regex:/^(?=[a-zA-Z0-9._]{3,30}$)(?!.*[_.]{2})[^_.].*[^_.]$/'],
            'password' => ['required']
        ] ,[
            'username.regex' => 'Invalid Username Pattern'
        ]);



        if ($validate->fails()){
            return Response::push([
                'errors' => $validate->errors()
            ] , 'Invalid Login Data Or User Not Registerd' , 400);
        
        }


        $user = \App\Models\User::where('username' , request()->username)->first();

        $requestPass = (request()->input('password'));


        if (!\Illuminate\Support\Facades\Hash::check($requestPass, $user->password)){

            return Response::push([], 'Invalid Login Data Or User Not Registerd' , 400);        

        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return Response::push([
            'token' => $token
        ], 'User Login Success' , 200);

    }


    function logout(){
        
        request()->user->tokens->each(function ($token) {
            $token->delete();
        });

        return Response::push(message : 'Logout Success');

    }

}
