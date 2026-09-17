<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_configurations', function (Blueprint $table) {
            $table->string('tutor_contact_phone')->nullable()->after('interstitial_ad_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('ad_configurations', function (Blueprint $table) {
            $table->dropColumn('tutor_contact_phone');
        });
    }
};