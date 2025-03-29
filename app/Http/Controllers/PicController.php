<?php

namespace App\Http\Controllers;

use App\Models\Pic;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\Response;

class PicController extends Controller{


    

    public function store(){
            $imgSize = 1024 * 40;

            $picCategory = [

                'ANIME' => 'anime pics',
                'NATURE' => 'nature pics',
                'ANIMALS' => 'animals pics',
                'MEME' => 'meme pics',
                'WALLPAPER' => 'wallpapers pics',
                'GAMES' => 'games pics'
    
            ];

            $keysStr = implode(',' , array_keys($picCategory));

            $validate = Validator::make(request()->only('pic' , 'descreption' , 'category') , [
                'pic' => ['image' , 'mimes:jpeg,png,gif','required' , "max:$imgSize"],
                'descreption' => ['required' , 'min:5'],
                'category' => ['required' , "in:$keysStr"],

            ] , [
                'category.in' => "Unknown Category Name Must Be in ($keysStr)"
            ]) ;

            if ($validate->fails()){
                return Response::push(["errors" => $validate->errors()] , "Invalid Pic Post Data" , 400);
            }

            $image  = request()->file('pic')->store('pics' , 'public');
        
            $desc = request()->input('descreption');

            $category = request()->input('category');

            $userCreator = request()->user->id;

            $picSchema = [
                'path' => $image,
                'descreption' => $desc,
                'user_id' => $userCreator,
                'category' => $category
            ];

            $pic = Pic::create($picSchema);
            return Response::push([
                'pic' => $pic
            ], 'Pic Created Successfully' , 201);

    }

    public function destroy(Pic $pic){

        if(!$pic){
            return Response::push([] , "Invalid Pic Post Data" , 400);
        }

        $userId = request()->user->id;

        if ($pic->user_id !== $userId){

            return Response::push([] , "Unable To Delete Pic" , 400);

        }


        $pic->delete();

        return Response::push(message:"Pic Deleted Successfully" ,status: 200);

    }



    public function update($pId){

        // $pic = DB::table('pics')->find($pId);

        $userId = request()->user->id;
        $pic = Pic::whereId($pId)->where('user_id' , $userId)->first();
        
        if (!$pic){
            return Response::push([] , "Invalid Pic Post Data" , 400);
        }

        $imgSize = 1024 * 40;
        $picCategory = [

            'ANIME' => 'anime pics',
            'NATURE' => 'nature pics',
            'ANIMALS' => 'animals pics',
            'MEME' => 'meme pics',
            'WALLPAPER' => 'wallpapers pics',
            'GAMES' => 'games pics'

        ];

        $keysStr = implode(',' , array_keys($picCategory));

        $validate = Validator::make(request()->only('pic' , 'descreption','category') , [
            'pic' => ['image' , 'mimes:jpeg,png,gif', "max:$imgSize"],
            'descreption' => ['min:5'],
            'category' => "in:$keysStr"
        ], [
            'category.in' => "Unknown Category Name. Must Be in ($keysStr)"
        ]);

        if ($validate->fails()){
            return Response::push(["errors" => $validate->errors()] , "Invalid Pic Post Data To Update" , 400);
        }


        $keys = ['descreption' , 'category'];

      
        if (request()->hasFile('photo')){
                $path = request()->file('photo')->store('pics' , 'public');                
                $pic->path = $path;
        }


            foreach ($keys as $key){

                if (request()->has($key)){
                    $pic->$key = request()->input($key); 
                }

            }



            if (!empty(request()->only('descreption' , 'category' , 'photo'))){
                // DB::table('pics')->whereId($pId)->update($data);
                $pic->save();
                 

                return Response::push(["pic" => $pic] , "Pic Updated Successful" , 200);
            }



            return Response::push([] , "Select Pic Field To Edit (photo | descreption)" , 400);
    



    }



    public function search(){
       
       
       
        $categories = [

            'ANIME' => 'anime pics',
            'NATURE' => 'nature pics',
            'ANIMALS' => 'animals pics',
            'MEME' => 'meme pics',
            'WALLPAPER' => 'wallpapers pics',
            'GAMES' => 'games pics'

        ];

        

        $category = request()->input('category');

        $desc = request()->input('descreption');




        if (!($category || $desc)){
            return Response::push(
            message: "Invalid Search data or Invalid Category Try to Add Search Key (category | descreption)" 
            , status:400);
            
        }

        $searchResults =  Pic::where('descreption' , 'LIKE' , $desc ? "$desc%" : '')
                            ->orWhere('category' ,   strtoupper($category))
                            ->select('id' , 'path' , 'descreption','user_id')
                            ->take(30)
                            ->withCount('likes')
                            ->with('user')
                            ->get()
                            ->map(function ($pic) {
                                $pic->is_liked = $pic->likes()
                                                     ->where('user_id' , request()->user->id)
                                                     ->exists();
                                return $pic;
                            });

        return Response::push([
        'pics' => $searchResults
        ] , 'Search Done' , 200); 


    }


    public function like($id){
        
        $picExists = Pic::whereId($id)->exists();
        $userId = request()->user->id;
        $like = Like::where('user_id' , $userId)
                        ->where('pic_id' , $id)->first();
        if (!$picExists){
            return Response::push(message:'Unable To Like Pic' , status:400);
        }

        $shcema = [
            'user_id' =>$userId,
            'pic_id' => $id 
        ];

        if ($like){
            $like->delete();
            
        }else{
            Like::create($shcema);
        }

        return Response::push([
            'is_liked' => !$like
        ],'Like Changed Successfully' , 200);

    }



    public function comment($id){

        $action = strtoupper(request()->input('action'));

        $picExists = Pic::whereId($id)->exists();

        $userId = request()->user->id;

        if (!$picExists){
            return Response::push(message:'Unable To Comment Pic' , status:400);
        }
        // return Response::push(message:$action , status:400);

        
        if ($action === 'CREATE' ){

            $check = Validator::make(request()->only('content') , [
                'content' => ['required']
            ]);

            if ($check->fails()){
                return Response::push([
                    'errors' => $check->errors()
                ],'Invalid Comment Data' , 400);
                
            }



            $content = request()->input('content');
            
            $schema = [
                'pic_id' => $id,
                'user_id' => $userId,
                'content' => $content 
            ];

            $comment = Comment::create($schema);

            return Response::push([
                'comment' => $comment
            ],'Comment Created Successfully' ,201);

        }
         if ($action === 'DELETE'){
            $commentId = request()->input('id');

            $comment = Comment::whereId($commentId)->where('user_id' , $userId)->first();

            if ($comment){
                $comment->delete();
                return Response::push(message:'Comment Deleted Successfully' ,status:200);    
            }else{
                return Response::push(message:'Invalid id or Comment Not Created' ,status:400);    
            }

        }

       
        return Response::push(message:'Please Enter Action To Process (create | delete)' ,status:200);    

        

    }


}
