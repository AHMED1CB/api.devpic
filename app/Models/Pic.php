<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path',
        'descreption',
        'category'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }


    public function likes(){
        return $this->hasMany(Like::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

}
