<?php

use App\Services\Response;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return Response::push([],'No Page Response ' , 300);
});
