<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('media_url'); // Chemin de l'image/vidéo stockée
            $table->string('target_url')->nullable(); // Lien externe (ex: https://mon-site.com)
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
