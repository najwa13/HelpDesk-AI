<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientHelpPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        $query = Article::query()
            ->publies()
            ->with('categorie');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('contenu', 'like', "%{$search}%")
                    ->orWhereHas('categorie', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
            });
        }

        $articles = $query->latest('published_at')
            ->get();

        $categories = Category::orderBy('nom')->get(['id', 'nom']);

        return view('client.help.index', [
            'articles' => $articles,
            'categories' => $categories,
            'currentSearch' => $request->get('search', ''),
        ]);
    }

    public function show(Article $article): View
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        if (! $article->published_at) {
            abort(404);
        }

        $article->load('categorie');

        return view('client.help.show', compact('article'));
    }
}
