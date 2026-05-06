<?php

namespace App\Http\Resources\Server;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerConfigResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'value' => $this->value,
            'default_value' => $this->default_value,
            'type' => $this->type,
            'group' => $this->group,
        ];
    }
}
