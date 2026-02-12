<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->decimal('price',10,2)->default(0.00); // 0 = gratuit
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }
    public function down(){ Schema::dropIfExists('books'); }
};
