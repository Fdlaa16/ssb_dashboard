<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubPlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'back_number' => $this->back_number,
            'position' => $this->position,
            'is_captain' => $this->is_captain,
            'status' => $this->status,
            'club' => new ClubResource($this->whenLoaded('club')), // jika kamu ingin versi ringkas club
        ];
    }
}
