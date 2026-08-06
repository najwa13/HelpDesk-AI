<?php

namespace App\Services;

use App\Ai\Agents\TicketAnalyzer;
use App\Enums\TicketPriority;
use App\Models\Category;
use App\Models\Ticket;
use UnexpectedValueException;

class GhostwriterService
{
    public function analyser(Ticket $ticket): array
    {
        $categories = Category::query()
            ->orderBy('nom')
            ->pluck('nom')
            ->all();

        $priorites = array_map(
            fn (TicketPriority $priority) => $priority->value,
            TicketPriority::cases()
        );

        $response = (new TicketAnalyzer(
            categories: $categories,
            priorites: $priorites,
        ))->prompt(
            $this->prompt($ticket),
            provider: 'groq'
        );

        $resultat = [
            'resume' => $response['resume'],
            'categorie_proposee' => $response['categorie_proposee'],
            'priorite_proposee' => $response['priorite_proposee'],
            'brouillon_reponse' => $response['brouillon_reponse'],
        ];

        $this->validerResultat(
            $resultat,
            $categories,
            $priorites
        );

        return $resultat;
    }

    private function prompt(Ticket $ticket): string
    {
        return <<<PROMPT
        Analyse le ticket de support suivant.

        Titre :
        {$ticket->titre}

        Description :
        {$ticket->description}
        PROMPT;
    }

    private function validerResultat(
        array $resultat,
        array $categories,
        array $priorites
    ): void {
        if (! in_array(
            $resultat['categorie_proposee'],
            $categories,
            true
        )) {
            throw new UnexpectedValueException(
                'La catégorie proposée par l’IA est invalide.'
            );
        }

        if (! in_array(
            $resultat['priorite_proposee'],
            $priorites,
            true
        )) {
            throw new UnexpectedValueException(
                'La priorité proposée par l’IA est invalide.'
            );
        }
    }
}
