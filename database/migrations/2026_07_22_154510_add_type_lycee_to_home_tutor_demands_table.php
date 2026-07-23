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
        Schema::table('home_tutor_demands', function (Blueprint $table) {
            // Ajoute la colonne type_lycee après la colonne neighborhood
            $table->string('type_lycee')->nullable()->after('neighborhood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_tutor_demands', function (Blueprint $table) {
            // Permet d'annuler la migration proprement si besoin
            $table->dropColumn('type_lycee');
        });
    }
};