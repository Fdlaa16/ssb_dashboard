<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,
            'description' => $this->description,
            'players' => PlayerResource::collection($this->whenLoaded('players')),
            'club_players' => ClubPlayerResource::collection($this->whenLoaded('clubPlayer')),
        ];
    }
}
