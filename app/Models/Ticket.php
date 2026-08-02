<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'statut',
        'priorite',
        'client_id',
        'agent_id',
        'categorie_id',
    ];

    protected function casts(): array
    {
        return [
            'statut' => TicketStatus::class,
            'priorite' => TicketPriority::class,
        ];
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Category::class, 'categorie_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function suggestionsIA()
    {
        return $this->hasMany(AIAnalysis::class);
    }
}
