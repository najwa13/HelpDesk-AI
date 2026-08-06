<?php

namespace App\Jobs;

use App\Models\AiSuggestion;
use App\Models\Ticket;
use App\Services\GhostwriterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeTicketJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function handle(GhostwriterService $ghostwriter): void
    {
        $resultat = $ghostwriter->analyser($this->ticket);

        AiSuggestion::create([
            'resume' => $resultat['resume'],
            'categorie_proposee' => $resultat['categorie_proposee'],
            'priorite_proposee' => $resultat['priorite_proposee'],
            'brouillon_reponse' => $resultat['brouillon_reponse'],
            'statut' => 'en_attente_validation',
            'ticket_id' => $this->ticket->id,
        ]);
    }
}
