<?php

namespace App\Data\Images;

use App\Enums\ImageType;
use App\Http\Requests\Media\MediaImageUpdateRequest;
use App\Http\Requests\Series\SeriesImageUpdateRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class ImageUpdateData {
    public function __construct(
        public readonly string $mode,
        public readonly ImageType $imageType,
        public readonly User $user,
        public readonly bool $isAdmin = false,
        public readonly ?int $imageId = null,
        public readonly ?string $url = null,
        public readonly ?UploadedFile $file = null,
        public readonly array $deletedIds = [],
    ) {}

    public static function fromRequest(MediaImageUpdateRequest|SeriesImageUpdateRequest $request, User $user, bool $isAdmin = false): self {
        return new self(
            imageType: ImageType::from($request->validated('type')),
            mode: $request->validated('mode'),
            user: $user,
            isAdmin: $isAdmin,
            imageId: $request->validated('image_id'),
            url: $request->validated('url'),
            file: $request->file('image'),
            deletedIds: $request->validated('deleted_images') ?? [],
        );
    }
}
