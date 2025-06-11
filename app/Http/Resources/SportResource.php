<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,

            // Optional: daftar pemain yang mengikuti cabang olahraga ini
            'players' => PlayerResource::collection($this->whenLoaded('players')),

            // Optional: relasi sportPlayer jika ingin detail pivot (misalnya untuk future: posisi, status, dsb)
            'sport_players' => SportPlayerResource::collection($this->whenLoaded('sportPlayer')),
        ];
    }
}
