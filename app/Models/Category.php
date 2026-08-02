<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'nom',
        'description',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'categorie_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'categorie_id');
    }
}
