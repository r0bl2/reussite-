<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('teachers', function (Blueprint $table) {
            // 1. On supprime l'index unique existant
            $table->dropUnique('teachers_telephone_unique');
        });

        Schema::table('teachers', function (Blueprint $table) {
            // 2. On modifie la colonne pour qu'elle devienne nullable ET unique à nouveau
            $table->string('telephone')->nullable()->unique()->change();
        });
    }

    public function down()
    {
        Schema::table('teachers', function (Blueprint $table) {
            // En cas de rollback, on supprime l'index
            $table->dropUnique('teachers_telephone_unique');
        });

        Schema::table('teachers', function (Blueprint $table) {
            // On remet la colonne obligatoire et unique
            $table->string('telephone')->nullable(false)->unique()->change();
        });
    }
};