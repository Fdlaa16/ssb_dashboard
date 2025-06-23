<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingResource extends JsonResource
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
            'club' => $this->club->name ?? '',
            'total' => $this->total ?? '',
            'win' => $this->win ?? '',
            'draw' => $this->draw ?? '',
            'lose' => $this->lose ?? '',
            'goal_in' => $this->goal_in ?? '',
            'goal_conceded' => $this->goal_conceded ?? '',
            'goal_difference' => $this->goal_difference ?? '',
            'point' => $this->point ?? '',
        ];
    }
}
