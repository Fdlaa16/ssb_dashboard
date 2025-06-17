<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleTrainingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? '',
            'first_club' => $this->firstClub->name ?? '',
            'secound_club' => $this->secoundClub->name ?? '',
            'stadium' => $this->stadium->name ?? '',
            'schedule_date' => $this->schedule_date ?? '',
            'schedule_start_at' => $this->schedule_start_at ?? '',
            'schedule_end_at' => $this->schedule_end_at ?? '',
            'score' => $this->score ?? '',
            'status' => $this->status ?? '',
        ];
    }
}
