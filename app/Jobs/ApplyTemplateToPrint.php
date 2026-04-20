<?php

namespace App\Jobs;

use App\Models\Font;
use App\Models\GeneratedPrint;
use App\Models\Photo;
use App\Models\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApplyTemplateToPrint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(
        public GeneratedPrint $generatedPrint
    ) {}

    public function handle(): void
    {
        $this->generatedPrint->update(['status' => 'processing']);

        try {
            $photo    = $this->generatedPrint->photo;
            $template = $this->generatedPrint->template;
            $data     = $this->generatedPrint->print_data ?? [];

            // ImageManager instantiated here (not in constructor) to keep the job serializable.
            $manager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);

            // Load the original photo from S3
            $photoContent = Storage::disk('s3')->get($photo->url);
            $image = $manager->make($photoContent);
            $image->orientate();

            // Composite the overlay (PNG with transparency) on top of the photo
            if ($template->overlay_path) {
                $overlayContent = Storage::disk('s3')->get($template->overlay_path);
                $overlay = $manager->make($overlayContent);
                $overlay->resize($image->width(), $image->height());
                $image->insert($overlay, 'top-left', 0, 0);
            }

            // Render each text field
            $defaultFontPath = public_path('fonts/OpenSans-Bold.ttf');
            $tempFontFiles   = []; // temp files to clean up after

            foreach ($template->fields as $field) {
                $value = trim($data[$field['key']] ?? '');
                if ($value === '') {
                    continue;
                }

                $x = (int) round(($field['x'] / 100) * $image->width());
                $y = (int) round(($field['y'] / 100) * $image->height());

                $fontSize = (int) ($field['font_size'] ?? 60);
                $color    = $field['color'] ?? '#ffffff';
                $align    = $field['align'] ?? 'center';

                // Resolve font: custom uploaded font or fall back to default
                $fontPath = $defaultFontPath;
                if (!empty($field['font_id'])) {
                    $font = Font::find($field['font_id']);
                    if ($font) {
                        $tmp = $font->downloadToTemp();
                        $tempFontFiles[] = $tmp;
                        $fontPath = $tmp;
                    }
                }

                $image->text($value, $x, $y, function ($f) use ($fontPath, $fontSize, $color, $align) {
                    if (file_exists($fontPath)) {
                        $f->file($fontPath);
                    }
                    $f->size($fontSize);
                    $f->color($color);
                    $f->align($align);
                    $f->valign('middle');
                });
            }

            // Save the generated print to S3
            $outputKey = 'prints/' . $this->generatedPrint->id . '.jpg';
            Storage::disk('s3')->put($outputKey, (string) $image->encode('jpg', 92));

            // Clean up any temp font files
            foreach ($tempFontFiles as $tmp) {
                @unlink($tmp);
            }

            $this->generatedPrint->update([
                'output_path' => $outputKey,
                'status'      => 'completed',
            ]);
        } catch (\Throwable $e) {
            Log::error('ApplyTemplateToPrint failed', [
                'generated_print_id' => $this->generatedPrint->id,
                'error' => $e->getMessage(),
            ]);

            $this->generatedPrint->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
