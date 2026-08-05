<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Articles techniques.',
    ]);
});

test('un article appartient à une catégorie', function () {
    $article = Article::create([
        'titre' => 'Réinitialiser son mot de passe',
        'contenu' => 'Procédure de réinitialisation.',
        'categorie_id' => $this->category->id,
    ]);

    expect($article->categorie->id)
        ->toBe($this->category->id);
});

test('published_at est converti en datetime', function () {
    $article = Article::create([
        'titre' => 'Article publié',
        'contenu' => 'Contenu.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    expect($article->published_at)
        ->toBeInstanceOf(Carbon::class);
});

test('le scope publies retourne uniquement les articles publiés', function () {
    $articlePublie = Article::create([
        'titre' => 'Article publié',
        'contenu' => 'Contenu publié.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Article::create([
        'titre' => 'Article brouillon',
        'contenu' => 'Contenu brouillon.',
        'categorie_id' => $this->category->id,
        'published_at' => null,
    ]);

    $articles = Article::publies()->get();

    expect($articles)
        ->toHaveCount(1)
        ->and($articles->first()->id)
        ->toBe($articlePublie->id);
});
