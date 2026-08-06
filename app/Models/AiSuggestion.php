<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSuggestion extends Model
{
    protected $fillable = [
        'resume',
        'categorie_proposee',
        'priorite_proposee',
        'brouillon_reponse',
        'statut',
        'ticket_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
