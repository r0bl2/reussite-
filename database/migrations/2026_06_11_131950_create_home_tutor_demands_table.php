<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_tutor_demands', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('last_name');
            $table->string('first_name');
            $table->string('phone_number');
            $table->string('photo_path');
            $table->string('diploma_path');

            $table->json('levels');

            $table->string('status')->default('pending');
            $table->text('admin_comment')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_tutor_demands');
    }
};