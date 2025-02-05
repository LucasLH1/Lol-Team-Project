<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('lol_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Liaison avec users
            $table->string('riot_pseudo'); // Ex: "afasqdaaa"
            $table->string('riot_tag'); // Ex: "FCC"
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lol_profiles');
    }
};

