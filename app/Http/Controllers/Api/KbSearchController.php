<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\SearchLog;
use App\Services\KbSearchService;
use App\Services\TicketService;

class KbSearchController extends Controller
{
    public function __construct(
        private KbSearchService $kbSearchService,
        private TicketService $ticketService,
    ) {}

    public function search(SearchRequest $request)
    {
        $log = $this->kbSearchService->search(
            $request->validated('requete'),
            $request->user()
        );

        if ($log->article) {
            return response()->json([
                'trouve' => true,
                'article' => [
                    'id' => $log->article->id,
                    'titre' => $log->article->titre,
                    'contenu' => $log->article->contenu,
                ],
                'score_correspondance' => $log->score_correspondance,
                'search_log_id' => $log->id,
            ]);
        }

        return response()->json([
            'trouve' => false,
            'search_log_id' => $log->id,
            'message' => 'Aucun article trouvé, vous pouvez créer un ticket.',
        ]);
    }

    public function creerTicketDepuisRecherche(
        StoreTicketRequest $request,
        int $searchLogId
    ) {
        $searchLog = SearchLog::query()
            ->whereKey($searchLogId)
            ->where('client_id', $request->user()->id)
            ->firstOrFail();

        if ($searchLog->article_id !== null) {
            return response()->json([
                'message' => 'Un article correspondant existe déjà pour cette recherche.',
            ], 422);
        }

        if ($searchLog->ticket_id !== null) {
            return response()->json([
                'message' => 'Un ticket a déjà été créé depuis cette recherche.',
            ], 422);
        }

        $ticket = $this->ticketService->create(
            $request->validated(),
            $request->user()
        );

        $searchLog->update([
            'ticket_id' => $ticket->id,
            'resultat' => 'ticket_cree',
        ]);

        return new TicketResource(
            $ticket->load(['categorie', 'client'])
        );
    }
}
