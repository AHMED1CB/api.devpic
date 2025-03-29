<?php

namespace App\Http\Controllers;

use App\Services\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Follow;

class UserController extends Controller
{
    function editProfile(){

        $imageSize = 1024 * 40;   

        $validate = Validator::make(request()->only('username' , 'bio' , 'photo') , [
            'username' => ['regex:/^(?=[a-zA-Z0-9._]{3,30}$)(?!.*[_.]{2})[^_.].*[^_.]$/' , 'unique:users'],
            'bio' => ['min:3' , 'max:255'],
            'photo' => ['image','mimes:jpeg,png,gif', "max:$imageSize"]
        ],[
        
            'username.regex' => 'Invalid username Pattern'
        ]);

        if ($validate->fails()){
            return Response::push(["errors" => $validate->errors()] , "Invalid Profile Data" , 400);
        }

        $fields = ['username'  , 'bio'];

        $user = request()->user;


        foreach ($fields as $key){

            if (request()->has($key)){
                $user->$key = request()->input($key);
            }

        }


        if (request()->hasFile('photo')){

            $path = request()->file('photo')->store('users' , 'public');

            $user->photo = $path;

        }

        
        if (request()->has('bio') || request()->has('username') || request()->hasFile('photo')){

            
                    
            
                    // User::whereId($currentUserId)->update($data);
            
                    $user->save();
            
                    return Response::push([
                        'user' => $user,
                    ] , 'User Updated Successfuly' , 200);
        }


        return Response::push(message:'Please Add Field to Update it First (username | bio | photo)' ,status:400);
        
    }


    public function findUser($slug){

        
        $user = User::where('slug' , $slug)
                ->first();


        if (!$user|| $slug == request()->user->slug){

            return Response::push([],'Unable To Visit This User: '  ,400);
    
    
        }


        return Response::push([
            'user' => $user
        ], 'User Found Successfully' , 200);


    }


    public function follow($following_id){



        $follower_id = request()->user->id;
        
        $userExists = User::whereId($following_id)->exists();

        if ((int)$following_id === (int)$follower_id || !$userExists){
            return Response::push([] , 'Unable To Follow' , 400);
        }
        $shema = [
            'follower_id' => $follower_id,
            'following_id' => $following_id
        ] ;


        $follow = Follow::where('follower_id' , $follower_id )
                        ->where('following_id' , $following_id)
                        ->first();

        if ($follow){
            $follow->delete();
        }else{
            Follow::create($shema);
        }


        return Response::push([
            'is_following' => !$follow
        ] , 'Follow User Changed Successful' , 200);

    }


}
