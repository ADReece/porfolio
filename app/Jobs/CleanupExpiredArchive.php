<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleanupExpiredArchive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filename;

    /**
     * Create a new job instance.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;

        // Delay this job by 48 hours
        $this->delay(now()->addHours(48));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Delete from S3
        if (Storage::disk('s3')->exists('archives/' . $this->filename)) {
            Storage::disk('s3')->delete('archives/' . $this->filename);
            Log::info('Deleted expired archive from S3', ['filename' => $this->filename]);
        }
    }
}

