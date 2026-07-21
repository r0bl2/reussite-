<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('active')->default(false);
            $table->string('correction_path');
            $table->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('exercises'); }
};
