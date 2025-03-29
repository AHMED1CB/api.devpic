<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {


        $picCategory = [

            'ANIME' => 'anime pics',
            'NATURE' => 'nature pics',
            'ANIMALS' => 'animals pics',
            'MEME' => 'meme pics',
            'WALLPAPER' => 'wallpapers pics',
            'GAMES' => 'games pics'

        ];
        Schema::create('pics', function (Blueprint $table) use($picCategory) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('path');

            $table->text('descreption');

            $table->enum('category' , array_keys($picCategory));

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pics');
    }
};
