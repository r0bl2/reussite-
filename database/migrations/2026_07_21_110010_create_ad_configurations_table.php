<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_configurations', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_ads')->default(true);
            $table->string('banner_ad_unit_id')->nullable();
            $table->string('interstitial_ad_unit_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_configurations');
    }
};