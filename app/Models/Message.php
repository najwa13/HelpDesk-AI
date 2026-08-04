<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'contenu',
        'ticket_id',
        'auteur_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
