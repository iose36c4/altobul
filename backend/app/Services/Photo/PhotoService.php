<?php

namespace App\Services\Photo;

use App\Exceptions\PhotoLimitReachedException;
use App\Jobs\ProcessPhotoJob;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PhotoService
{
    public const MAX_PHOTOS_PER_USER = 32;

    public const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

    public const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function upload(User $user, UploadedFile $file, string $visibility, bool $requiresVerified = false): Photo
    {
        $photoCount = Photo::where('user_id', $user->id)
            ->where('status', '!=', 'DELETED')
            ->count();

        if ($photoCount >= self::MAX_PHOTOS_PER_USER) {
            throw new PhotoLimitReachedException(self::MAX_PHOTOS_PER_USER);
        }

        $this->validateFile($file);

        $photoId = Str::uuid();
        $originalExtension = $file->getClientOriginalExtension();
        $tempPath = "uploads/{$user->id}/{$photoId}.{$originalExtension}";

        Storage::disk('local')->put($tempPath, file_get_contents($file->getRealPath()));

        $photo = Photo::create([
            'id' => $photoId,
            'user_id' => $user->id,
            'storage_key' => $tempPath,
            'mime_type' => $file->getMimeType(),
            'width' => 0,
            'height' => 0,
            'size_bytes' => $file->getSize(),
            'visibility' => $visibility,
            'requires_verified' => $requiresVerified,
            'status' => 'PROCESSING',
        ]);

        ProcessPhotoJob::dispatch($photo->id, $tempPath);

        return $photo;
    }

    private function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new ValidationException(
                Validator::make(
                    ['file' => $file],
                    ['file' => 'max:'.(self::MAX_FILE_SIZE / 1024)]
                )
            );
        }

        $realMimeType = $this->getRealMimeType($file);

        if (! in_array($realMimeType, self::ALLOWED_MIME_TYPES)) {
            throw new ValidationException(
                Validator::make(
                    ['file' => $file],
                    ['file' => 'mimes:jpeg,png,webp']
                )
            );
        }
    }

    private function getRealMimeType(UploadedFile $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $realMimeType = $finfo->file($file->getRealPath());

        if ($realMimeType === false) {
            return $file->getMimeType();
        }

        return $realMimeType;
    }
}
