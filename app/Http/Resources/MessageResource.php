<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'contenu' => $this->contenu,
            'auteur' => [
                'id' => $this->auteur->id,
                'name' => $this->auteur->name,
                'role' => $this->auteur->role->value,
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
