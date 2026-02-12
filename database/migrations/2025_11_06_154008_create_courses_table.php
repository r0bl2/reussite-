<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->nullOnDelete();

            $table->foreignId('subject_id')
                  ->nullable()
                  ->constrained('subjects')
                  ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();

            $table->enum('level', [
                '6ème','5ème','4ème','3ème',
                'Seconde','Première','Terminale'
            ])->nullable();

            $table->string('class')->nullable();
            $table->boolean('active')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
