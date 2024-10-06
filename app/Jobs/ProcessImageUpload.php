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
use Illuminate\Support\Facades\Auth;

class ProcessImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        // Get the currently authenticated user's username
        $this->username = Auth::user()->username;
        $this->filepath = $this->user->id."/".$this->fileName;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Retrieve the file from the temporary storage
        $temporaryFile = storage_path('app/' . $this->temporaryPath);

        // Upload the full-resolution image to S3
        $photo = $this->uploadFullResolutionImage($temporaryFile);

        // Generate a thumbnail and upload it to S3
        $thumbnail = $this->createThumbnail($temporaryFile);
        Storage::disk('s3')->put("thumbnails/".$this->filepath, $thumbnail->stream());

        // Process the image to create a watermarked version
        $watermarkedImage = $this->processWatermarkedImage($temporaryFile);
        Storage::disk('s3')->put("watermarked/".$this->filepath, $watermarkedImage->stream());

        // Store the photo information in the database
        $this->set->photos()->create([
            'user_id' => $this->user->id,
            'url' => "photos/{$this->filepath}",
            'private' => $this->set->collection->private,
            'size' => $photo,
        ]);

        // Remove the temporary file after processing
        Storage::delete($this->temporaryPath);
    }

    /**
     * Upload the full-resolution image to S3.
     */
    protected function uploadFullResolutionImage($file)
    {
        // Upload the full-resolution image to S3
        Storage::disk('s3')->put("photos/".$this->filepath, file_get_contents($file));
        return Storage::disk('s3')->size("photos/".$this->filepath);
    }

    /**
     * Create a thumbnail of the image while retaining the aspect ratio.
     */
    protected function createThumbnail($file)
    {
        // Load the image using ImageManager
        $image = $this->imageManager->make($file);

        // Resize the image to a thumbnail (e.g., max width or height of 300px, keeping aspect ratio)
        $image->resize(300, 300, function ($constraint) {
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

        // Apply the tiled watermark with the username
        $this->applyTextWatermark($image);

        return $image;
    }

    /**
     * Apply a tiled text watermark to the image.
     */
    protected function applyTextWatermark($image)
    {
        $text = '@'.$this->username;  // Get the username of the authenticated user
        $fontSize = 100;
        $textColor = 'rgba(255, 255, 255, 0.5)';  // White text with 50% opacity
        $tileSpacing = 600;  // Distance between each watermark tile

        // Get image dimensions
        $imageWidth = $image->width();
        $imageHeight = $image->height();

        // Loop to tile the watermark over the image
        for ($y = 0; $y < $imageHeight; $y += $fontSize) {
            for ($x = 0; $x < $imageWidth; $x += $tileSpacing) {
                // Draw the text on the image
                $image->text($text, $x, $y, function ($font) use ($fontSize, $textColor) {
                    $font->file(public_path('fonts/OpenSans-Bold.ttf'));  // Optional: Specify a font
                    $font->size($fontSize);
                    $font->color($textColor);
                    $font->align('left');
                    $font->valign('top');
                });
            }
        }
    }
}
