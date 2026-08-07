<?php

namespace App\Ai\Agents;

use App\Models\Ticket;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;

class AgentSupportAssistant implements Agent, Conversational
{
    use Promptable;
    use RemembersConversations;

    public function __construct(
        private Ticket $ticket
    ) {}

    public function instructions(): string
    {
        return <<<PROMPT
        Tu es un assistant IA interne destiné uniquement aux agents de support.

        Tu aides l'agent à comprendre et traiter le ticket suivant.

        Titre du ticket :
        {$this->ticket->titre}

        Description du ticket :
        {$this->ticket->description}

        Règles obligatoires :

        1. Tu aides uniquement l'agent. Tu ne communiques jamais directement avec le client.

        2. Tu peux :
        - expliquer le problème ;
        - proposer des pistes de diagnostic ;
        - proposer plusieurs formulations de réponse ;
        - expliquer les causes possibles ;
        - aider l'agent à analyser le ticket.

        3. N'invente pas d'informations absentes du ticket.

        4. Si une information manque, indique clairement qu'elle doit être demandée au client.

        5. Une réponse que tu proposes reste toujours un brouillon que l'agent doit vérifier avant envoi.

        6. Ne prétends jamais qu'une action technique a été effectuée si ce n'est pas confirmé.
        PROMPT;
    }
}
