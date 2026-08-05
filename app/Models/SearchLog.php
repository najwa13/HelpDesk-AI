<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $fillable = [
        'requete_originale',
        'requete_normalisee',
        'langue_detectee',
        'resultat',
        'score_correspondance',
        'client_id',
        'article_id',
        'ticket_id',
    ];

    protected function casts(): array
    {
        return [
            'score_correspondance' => 'decimal:4',
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
