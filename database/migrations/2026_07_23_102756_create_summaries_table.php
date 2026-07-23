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
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->nullOnDelete(); // Si le prof est supprimé, le résumé reste en base sans enseignant

            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->cascadeOnDelete(); // Si la matière est supprimée, les résumés associés le sont aussi

            // Informations du résumé
            $table->string('title');
            $table->text('description');
            $table->string('file_path');

            // Filtrage école / classe
            $table->string('level')->nullable(); // Ex: Secondaire, Primaire...
            $table->string('class')->nullable(); // Ex: 3ème A, Terminale C...

            // Statut de validation par l'admin
            $table->boolean('active')->default(false);

            $table->timestamps();

            // Index pour optimiser les requêtes de filtrage fréquentes
            $table->index(['level', 'class', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};