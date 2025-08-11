<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StructureResource extends JsonResource
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
            'code' => $this->code ?? '',
            'name' => $this->name ?? '',
            'department' => $this->department ?? '',
            'date_of_birth' => $this->date_of_birth ?? '',
            'user' => [
                'id' => $this->user->id ?? '',
                'email' => $this->user->email ?? '',
            ]
        ];
    }
}
