<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? '',
            'code' => $this->code ?? '',
            'name' => $this->name ?? '',
            'nisn' => $this->nisn ?? '',
            'height' => $this->height ?? '',
            'weight' => $this->weight ?? '',
            'email' => $this->user->email ?? '',
            'password' => $this->user->password ?? '',

            // many-to-many (tanpa pivot)
            'clubs' => ClubResource::collection($this->whenLoaded('clubs')),

            // one-to-many: detail pivot seperti back_number, position
            'club_players' => ClubPlayerResource::collection($this->whenLoaded('clubPlayers')),

            // many-to-many (tanpa pivot)
            'sports' => SportResource::collection($this->whenLoaded('sports')),

            // one-to-many: detail pivot sport-player
            'sport_players' => SportPlayerResource::collection($this->whenLoaded('sportPlayer')),
        ];
    }
}
