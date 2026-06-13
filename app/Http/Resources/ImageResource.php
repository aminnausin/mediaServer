<?php

namespace App\Http\Resources;

use App\Enums\ImageSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImageResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'path' => asset("/storage/{$this->path}"),
            'type' => $this->image_type,
            'source' => $this->user_id && $this->image_source === ImageSource::DOWNLOADED ? ImageSource::UPLOADED : $this->image_source,
            'blur_hash' => $this->blur_hash,
            'user_id' => $this->user_id === Auth::id() || Gate::allows('admin') ? $this->user_id : null,
            'replaced_at' => $this->replaced_at,
        ];
    }
}
