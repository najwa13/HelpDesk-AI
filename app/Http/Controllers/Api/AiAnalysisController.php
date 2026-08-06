<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAiSuggestionRequest;
use App\Jobs\AnalyzeTicketJob;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAnalysisController extends Controller
{
    public function analyze(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('updateStatus', $ticket);

        AnalyzeTicketJob::dispatch($ticket);

        return response()->json([
            'message' => 'Analyse IA lancée.',
        ], 202);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('view', $ticket);

        $suggestion = $ticket->suggestionsIA()
            ->latest('id')
            ->first();

        if (! $suggestion) {
            return response()->json([
                'message' => 'Aucune analyse IA disponible pour ce ticket.',
            ], 404);
        }

        return response()->json([
            'data' => $this->suggestionData($suggestion),
        ]);
    }

    public function update(
        UpdateAiSuggestionRequest $request,
        Ticket $ticket
    ): JsonResponse {
        $this->authorize('updateStatus', $ticket);

        $suggestion = $ticket->suggestionsIA()
            ->latest('id')
            ->first();

        if (! $suggestion) {
            return response()->json([
                'message' => 'Aucune analyse IA disponible pour ce ticket.',
            ], 404);
        }

        if ($suggestion->statut !== 'en_attente_validation') {
            return response()->json([
                'message' => 'Cette suggestion IA a déjà été validée.',
            ], 422);
        }

        $suggestion->update([
            'brouillon_reponse' => $request->validated('brouillon_reponse'),
        ]);

        return response()->json([
            'message' => 'Brouillon IA modifié.',
            'data' => $this->suggestionData($suggestion->refresh()),
        ]);
    }

    public function validateSuggestion(
        Request $request,
        Ticket $ticket
    ): JsonResponse {
        $this->authorize('updateStatus', $ticket);

        $suggestion = $ticket->suggestionsIA()
            ->latest('id')
            ->first();

        if (! $suggestion) {
            return response()->json([
                'message' => 'Aucune analyse IA disponible pour ce ticket.',
            ], 404);
        }

        if ($suggestion->statut !== 'en_attente_validation') {
            return response()->json([
                'message' => 'Cette suggestion IA a déjà été validée.',
            ], 422);
        }

        $suggestion->update([
            'statut' => 'validee',
        ]);

        return response()->json([
            'message' => 'Suggestion IA validée.',
            'data' => $this->suggestionData($suggestion->refresh()),
        ]);
    }

    private function suggestionData($suggestion): array
    {
        return [
            'id' => $suggestion->id,
            'resume' => $suggestion->resume,
            'categorie_proposee' => $suggestion->categorie_proposee,
            'priorite_proposee' => $suggestion->priorite_proposee,
            'brouillon_reponse' => $suggestion->brouillon_reponse,
            'statut' => $suggestion->statut,
            'created_at' => $suggestion->created_at?->toISOString(),
            'updated_at' => $suggestion->updated_at?->toISOString(),
        ];
    }
}
