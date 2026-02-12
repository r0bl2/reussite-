<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('corrections'); }
};
