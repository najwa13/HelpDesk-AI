<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminManagementPageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $agents = User::query()
            ->where('role', UserRole::Agent)
            ->withCount('ticketsAffectes')
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->withCount(['tickets', 'articles'])
            ->orderBy('nom')
            ->get();

        return view('admin.management', [
            'agents' => $agents,
            'categories' => $categories,
        ]);
    }

    public function storeCategory(StoreCategoryRequest $request)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        Category::create($request->validated());

        return back()->with('success', 'Catégorie créée.');
    }

    public function destroyCategory(Category $category)
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $inUse = $category->tickets()->exists() || $category->articles()->exists();

        if ($inUse) {
            return back()->withErrors([
                'category' => 'Impossible de supprimer une catégorie utilisée par des tickets ou des articles.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
