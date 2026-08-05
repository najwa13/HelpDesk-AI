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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('contenu');

            $table->foreignId('categorie_id')
                ->constrained('categories');

            $table->timestamp('published_at')
                ->nullable();

            $table->timestamps();
        });
        DB::statement(
            'ALTER TABLE articles ADD FULLTEXT fulltext_titre_contenu (titre, contenu)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
