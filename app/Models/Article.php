<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'titre',
        'contenu',
        'categorie_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function categorie()
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function scopePublies(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }
}
