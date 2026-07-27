<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ProcessPhotoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public string $photoId,
        public string $tempPath,
    ) {}

    public function handle(): void
    {
        $photo = Photo::find($this->photoId);

        if (! $photo || $photo->status !== 'PROCESSING') {
            $this->cleanupTempFile();

            return;
        }

        $tempFullPath = Storage::disk('local')->path($this->tempPath);

        if (! file_exists($tempFullPath)) {
            $this->markFailed($photo, 'Temp file not found');

            return;
        }

        try {
            $manager = new ImageManager(['driver' => 'gd']);

            $image = $manager->read($tempFullPath);

            $image->orient();

            $maxDimension = 1024;
            if ($image->width() > $maxDimension || $image->height() > $maxDimension) {
                $image->coverDown($maxDimension, $maxDimension);
            }

            $webpContents = $image->toWebp(85)->encode();

            $finalWidth = $image->width();
            $finalHeight = $image->height();
            $finalSize = strlen($webpContents);

            $s3Key = "users/{$photo->user_id}/{$photo->id}.webp";

            Storage::disk('s3')->put($s3Key, $webpContents, 'public');

            $photo->update([
                'storage_key' => $s3Key,
                'mime_type' => 'image/webp',
                'width' => $finalWidth,
                'height' => $finalHeight,
                'size_bytes' => $finalSize,
                'status' => 'ACTIVE',
            ]);

            $this->cleanupTempFile();

        } catch (\Exception $e) {
            Log::error('Photo processing failed', [
                'photo_id' => $this->photoId,
                'error' => $e->getMessage(),
            ]);

            $this->markFailed($photo, $e->getMessage());
        }
    }

    private function markFailed(Photo $photo, string $reason): void
    {
        $photo->update(['status' => 'DELETED']);

        $this->cleanupTempFile();
    }

    private function cleanupTempFile(): void
    {
        try {
            if (Storage::disk('local')->exists($this->tempPath)) {
                Storage::disk('local')->delete($this->tempPath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup temp photo file', [
                'path' => $this->tempPath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
