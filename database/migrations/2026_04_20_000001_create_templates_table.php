<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();

            // S3 path to the PNG overlay (transparent areas let the photo show through)
            $table->string('overlay_path')->nullable();

            // JSON array of text field definitions:
            // [{ "key": "player_name", "label": "Player Name", "x": 50, "y": 85,
            //    "font_size": 60, "color": "#ffffff", "align": "center",
            //    "font_weight": "bold" }]
            $table->json('fields')->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
