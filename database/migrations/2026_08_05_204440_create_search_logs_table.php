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
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->text('requete_originale');
            $table->text('requete_normalisee');

            $table->string('langue_detectee', 10)->nullable();

            $table->string('resultat');

            $table->decimal('score_correspondance', 8, 4)->nullable();

            $table->foreignId('client_id')
                ->constrained('users');

            $table->foreignId('article_id')
                ->nullable()
                ->constrained('articles');

            $table->foreignId('ticket_id')
                ->nullable()
                ->unique()
                ->constrained('tickets');

            $table->timestamps();

            $table->index('resultat');
            $table->index('client_id');
            $table->index('article_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
