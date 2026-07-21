<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type_lycee'); // Recommandé : college, lycee_moderne, lycee_technique
            $table->timestamps();
            $table->unique(['name', 'type_lycee']);
        });
    }
    public function down(){ Schema::dropIfExists('subjects'); }
};
