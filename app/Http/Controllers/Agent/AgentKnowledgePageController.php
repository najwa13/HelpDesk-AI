<?php

namespace App\Http\Controllers\Agent;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentKnowledgePageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        $query = Article::query()
            ->publies()
            ->with('categorie');

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('categorie_id', $request->category);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('contenu', 'like', "%{$search}%")
                    ->orWhereHas('categorie', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
            });
        }

        $articles = $query->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('nom')->get(['id', 'nom']);

        return view('agent.knowledge.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    public function show(Article $article)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Agent) {
            abort(403);
        }

        // Only allow viewing published articles
        if (! $article->published_at) {
            abort(404);
        }

        $article->load('categorie');

        return view('agent.knowledge.show', compact('article'));
    }
}
