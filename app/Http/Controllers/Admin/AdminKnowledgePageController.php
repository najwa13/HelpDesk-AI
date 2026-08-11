<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminKnowledgePageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $query = Article::query()->with('categorie');

        if ($request->filled('category')) {
            $query->where('categorie_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('contenu', 'like', "%{$search}%")
                    ->orWhereHas('categorie', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
            });
        }

        $articles = $query->latest('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('nom')->get(['id', 'nom']);

        return view('admin.knowledge.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    public function show(Article $article)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $article->load('categorie');

        return view('admin.knowledge.show', compact('article'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $categories = Category::orderBy('nom')->get(['id', 'nom']);

        return view('admin.knowledge.form', [
            'article' => null,
            'categories' => $categories,
        ]);
    }

    public function store(StoreArticleRequest $request)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $data = $request->validated();
        $data['published_at'] = $request->boolean('publier')
            ? now()
            : null;

        Article::create($data);

        return redirect()->route('admin.knowledge.index')
            ->with('success', 'Article créé.');
    }

    public function edit(Article $article)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $categories = Category::orderBy('nom')->get(['id', 'nom']);

        return view('admin.knowledge.form', [
            'article' => $article,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $data = $request->validated();
        $data['published_at'] = $request->boolean('publier')
            ? now()
            : null;

        $article->update($data);

        return redirect()->route('admin.knowledge.index')
            ->with('success', 'Article mis à jour.');
    }

    public function destroy(Article $article)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $article->delete();

        return redirect()->route('admin.knowledge.index')
            ->with('success', 'Article supprimé.');
    }
}
