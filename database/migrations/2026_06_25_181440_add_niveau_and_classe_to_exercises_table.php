<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // Ces champs sont obligatoires (NOT NULL en SQL)
            // et seront placés proprement juste après le titre
            $table->string('niveau')->after('title');
            $table->string('classe')->after('niveau');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            // Permet de faire marche arrière avec "php artisan migrate:rollback"
            $table->dropColumn(['niveau', 'classe']);
        });
    }
};