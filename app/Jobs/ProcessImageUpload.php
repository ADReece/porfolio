<?php

namespace App\Jobs;

use App\Models\Set;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public $user;
    public $temporaryPath;
    public $set;
    public $fileName;
    public $username;
    public $filepath;
    protected $imageManager; // ImageManager instance

    /**
     * Create a new job instance.
     *
     * @param string $temporaryPath
     * @param Set $set
     * @param string $fileName
     */
    public function __construct($user, $temporaryPath, Set $set, $fileName)
    {
        // Assign the constructor parameters to the class properties
        $this->user = $user;
        $this->temporaryPath = $temporaryPath;
        $this->set = $set;
        $this->fileName = $fileName;
        $this->imageManager = new ImageManager(['driver' => 'imagick']);

        // Use the passed user's username
        $this->username = $user->username;
        $this->filepath = $this->user->id."/".$this->fileName;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $temporaryFile = storage_path('app/' . $this->temporaryPath);

        if (!is_file($temporaryFile)) {
            throw new RuntimeException('Temporary upload file is missing: '.$this->temporaryPath);
        }

        try {
            $photoSize = $this->uploadFullResolutionImage($temporaryFile);

            $thumbnail = $this->createThumbnail($temporaryFile);
            Storage::disk('s3')->put('thumbnails/'.$this->filepath, $thumbnail->stream()->__toString());

            $watermarkedImage = $this->processWatermarkedImage($temporaryFile);
            Storage::disk('s3')->put('watermarked/'.$this->filepath, $watermarkedImage->stream()->__toString());

            $this->set->photos()->create([
                'user_id' => $this->user->id,
                'url' => "photos/{$this->filepath}",
                'private' => $this->set->collection->private,
                'size' => $photoSize,
            ]);
        } finally {
            Storage::delete($this->temporaryPath);
        }
    }

    /**
     * Upload the full-resolution image to S3.
     */
    protected function uploadFullResolutionImage($file)
    {
        // Load the image and fix orientation based on EXIF data
        $image = $this->imageManager->make($file);
        $image->orientate();

        // Upload the full-resolution image to S3
        Storage::disk('s3')->put("photos/".$this->filepath, $image->stream()->__toString());
        return Storage::disk('s3')->size("photos/".$this->filepath);
    }

    /**
     * Create a thumbnail of the image while retaining the aspect ratio.
     */
    protected function createThumbnail($file)
    {
        // Load the image using ImageManager
        $image = $this->imageManager->make($file);

        // Fix orientation based on EXIF data
        $image->orientate();

        // Resize the image to a thumbnail (e.g., max width or height of 300px, keeping aspect ratio)
        $image->resize(800, 800, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize(); // Avoid enlarging the image if it's smaller than the target dimensions
        });

        return $image;
    }

    /**
     * Process the image by adding a tiled watermark with the username.
     */
    protected function processWatermarkedImage($file)
    {
        // Load the image using ImageManager
        $image = $this->imageManager->make($file);

        // Fix orientation based on EXIF data
        $image->orientate();

        // Apply the tiled watermark with the username
        $this->applyTextWatermark($image);

        return $image;
    }

    /**
     * Apply a tiled text watermark to the image - VERY OBNOXIOUS!
     */
    protected function applyTextWatermark($image)
    {
        // Use custom watermark text or default to @username
        $text = $this->user->watermark_text ?: '@'.$this->username;

        // Make it MUCH more visible and repeated
        $fontSize = 80;  // Slightly smaller but more frequent
        $textColor = 'rgba(255, 255, 255, 0.6)';  // More opaque (60% vs 50%)
        $horizontalSpacing = 300;  // Closer together (300 vs 600)
        $verticalSpacing = 150;    // Much closer vertically (150 vs 100)

        // Get image dimensions
        $imageWidth = $image->width();
        $imageHeight = $image->height();

        // Loop to tile the watermark VERY DENSELY over the image
        for ($y = -$fontSize; $y < $imageHeight + $fontSize; $y += $verticalSpacing) {
            for ($x = -200; $x < $imageWidth + 200; $x += $horizontalSpacing) {
                // Draw the text on the image at a 45-degree angle for extra obnoxiousness
                $image->text($text, $x, $y, function ($font) use ($fontSize, $textColor) {
                    // Use custom font if it exists, otherwise use system default
                    $fontPath = public_path('fonts/OpenSans-Bold.ttf');
                    if (file_exists($fontPath)) {
                        $font->file($fontPath);
                    }
                    $font->size($fontSize);
                    $font->color($textColor);
                    $font->align('center');
                    $font->valign('middle');
                    $font->angle(-45);  // Diagonal watermark
                });
            }
        }

        // Add a second layer with different angle for MAXIMUM obnoxiousness
        for ($y = 0; $y < $imageHeight; $y += $verticalSpacing * 1.5) {
            for ($x = 150; $x < $imageWidth; $x += $horizontalSpacing) {
                $image->text($text, $x, $y, function ($font) use ($fontSize, $textColor) {
                    $fontPath = public_path('fonts/OpenSans-Bold.ttf');
                    if (file_exists($fontPath)) {
                        $font->file($fontPath);
                    }
                    $font->size($fontSize * 0.8);  // Slightly smaller for variety
                    $font->color($textColor);
                    $font->align('center');
                    $font->valign('middle');
                    $font->angle(45);  // Opposite diagonal
                });
            }
        }
    }
}
