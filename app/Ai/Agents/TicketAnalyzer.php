<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class TicketAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private array $categories,
        private array $priorites,
    ) {}

    public function instructions(): string
    {
        $categories = implode(', ', $this->categories);
        $priorites = implode(', ', $this->priorites);

        return <<<PROMPT
        Tu es un assistant interne pour des agents de support d'une entreprise SaaS.

        Ton rôle est uniquement d'aider l'agent à analyser un ticket.

        Tu dois fournir :
        - un résumé court du problème ;
        - une catégorie proposée ;
        - une priorité proposée ;
        - un brouillon de réponse professionnel.

        Règles obligatoires :

        1. La catégorie proposée doit être exactement l'une des catégories suivantes :
        {$categories}

        2. La priorité proposée doit être exactement l'une des valeurs suivantes :
        {$priorites}

        3. Le résumé doit être court et factuel.

        4. Le brouillon ne doit pas inventer d'informations absentes du ticket.

        5. Le brouillon est seulement une suggestion destinée à l'agent.
        Il ne sera jamais envoyé automatiquement au client.

        6. Ne crée aucune nouvelle catégorie ou priorité.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'resume' => $schema
                ->string()
                ->required(),

            'categorie_proposee' => $schema
                ->string()
                ->required(),

            'priorite_proposee' => $schema
                ->string()
                ->required(),

            'brouillon_reponse' => $schema
                ->string()
                ->required(),
        ];
    }
}
