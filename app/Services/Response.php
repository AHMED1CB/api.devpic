<?php

namespace App\Services;


class Response {

    public static function push($data = [] , $message = '' , $status = 200){

        try {
            return response()->json([
                'message' => $message ,
                'status' => $status < 400 ? 'Success' : 'Fail',
                'statusCode' => $status,
                'data' => $data 
            ] , $status);
        }catch(\Exception){
            
            return response()->json([
                'message' => 'Internal Server Error',
                'status' => 'Faiil',
                'statusCode' => 500,
                'data' => []
            ] , 500);

        }

    }

}