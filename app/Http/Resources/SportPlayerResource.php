<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SportPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player' => new PlayerResource($this->whenLoaded('player')),
            'sport' => new SportResource($this->whenLoaded('sport')),
        ];
    }
}
