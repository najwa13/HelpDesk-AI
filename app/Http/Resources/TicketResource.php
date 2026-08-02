<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'description' => $this->description,
            'statut' => $this->statut->value,
            'priorite' => $this->priorite?->value,
            'categorie' => $this->categorie->nom,
            'client' => $this->client->name,
            'agent' => $this->agent?->name,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
