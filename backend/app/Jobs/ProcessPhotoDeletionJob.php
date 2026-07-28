<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public string $storageKey,
    ) {}

    public function handle(): void
    {
        try {
            if (Storage::disk('s3')->exists($this->storageKey)) {
                Storage::disk('s3')->delete($this->storageKey);

                Log::info('Photo deleted from S3', [
                    'storage_key' => $this->storageKey,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete photo from S3', [
                'storage_key' => $this->storageKey,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
