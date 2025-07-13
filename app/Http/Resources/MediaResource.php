<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'type_media' => $this->type_media ?? '',
            'title' => $this->title ?? '',
            'description' => $this->description ?? '',
            'link' => $this->link ?? '',
            'status' => $this->status ?? '',
        ];
    }
}
