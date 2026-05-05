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
            'key' => $this->key,
            'value' => json_decode($this->value, true),
            'default_value' => json_decode($this->default_value, true),
            'type' => $this->type,
            'group' => $this->group,
        ];
    }
}
