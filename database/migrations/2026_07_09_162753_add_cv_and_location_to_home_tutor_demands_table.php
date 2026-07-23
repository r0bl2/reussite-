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
            // Ajout des nouveaux champs après le champ 'diploma_path'
            $table->string('cv_path')->after('diploma_path');
            $table->string('city', 100)->after('phone_number');
            $table->string('neighborhood', 100)->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_tutor_demands', function (Blueprint $table) {
            
            $table->dropColumn(['cv_path', 'city', 'neighborhood']);
        });
    }
};