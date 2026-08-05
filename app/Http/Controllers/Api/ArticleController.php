<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ArticleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('manage', Article::class);

        $articles = Article::with('categorie')
            ->latest()
            ->get();

        return ArticleResource::collection($articles);
    }

    public function store(StoreArticleRequest $request): ArticleResource
    {
        $this->authorize('manage', Article::class);

        $article = Article::create($request->validated());

        $article->load('categorie');

        return new ArticleResource($article);
    }

    public function show(Article $article): ArticleResource
    {
        $this->authorize('manage', Article::class);

        $article->load('categorie');

        return new ArticleResource($article);
    }

    public function update(
        UpdateArticleRequest $request,
        Article $article
    ): ArticleResource {
        $this->authorize('manage', Article::class);

        $article->update($request->validated());

        $article->load('categorie');

        return new ArticleResource($article);
    }

    public function destroy(Article $article): Response
    {
        $this->authorize('manage', Article::class);

        $article->delete();

        return response()->noContent();
    }
}
