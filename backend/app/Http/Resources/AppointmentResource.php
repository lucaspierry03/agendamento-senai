<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'client_id' => $this->client_id,
            'client_name' => $this->client?->name,
            'date' => $this->date?->format('d/m/Y'),
            'date_raw' => $this->date?->format('Y-m-d'),
            'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
        ];
    }
}
