<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('telephone')->nullable();

            $table->string('type_lycee')->nullable();

            $table->string('level')->nullable();

            $table->string('class')->nullable();

            $table->json('matieres')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'telephone',
                'type_lycee',
                'level',
                'class',
                'matieres'
            ]);

        });
    }
};