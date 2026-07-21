<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
    
            $table->string('telephone')->unique();

            $table->string('type_lycee')->nullable();

            $table->string('level')->nullable();

            $table->string('class')->nullable();

            $table->json('matieres')->nullable();

            $table->string('diplome')->nullable();

            $table->string('photo')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teachers');
    }
};