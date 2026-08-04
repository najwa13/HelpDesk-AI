<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('manage', Category::class);

        return response()->json([
            'data' => Category::query()
                ->latest()
                ->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('manage', Category::class);

        $category = Category::create(
            $request->validated()
        );

        return response()->json([
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('manage', Category::class);

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $category->update($validated);

        return response()->json([
            'data' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        $this->authorize('manage', Category::class);

        $category->delete();

        return response()->noContent();
    }
}
