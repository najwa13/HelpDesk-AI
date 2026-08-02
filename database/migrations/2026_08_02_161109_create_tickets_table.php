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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');

            $table->enum('statut', [
                'ouvert',
                'en_cours',
                'resolu',
                'ferme',
            ])->default('ouvert');

            $table->enum('priorite', [
                'basse',
                'moyenne',
                'haute',
            ])->nullable();

            $table->foreignId('client_id')
                ->constrained('users');

            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('categorie_id')
                ->constrained('categories');
            $table->timestamps();
            $table->index('statut');
            $table->index('categorie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
