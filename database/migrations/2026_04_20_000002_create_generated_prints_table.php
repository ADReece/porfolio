<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_prints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('photo_id');
            $table->foreign('photo_id')->references('id')->on('photos')->onDelete('cascade');
            $table->uuid('template_id');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');

            // The values used for each text field during generation
            // e.g. { "player_name": "John Smith", "jersey_number": "12" }
            $table->json('print_data')->nullable();

            // S3 key of the generated output image
            $table->string('output_path')->nullable();

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_prints');
    }
};
