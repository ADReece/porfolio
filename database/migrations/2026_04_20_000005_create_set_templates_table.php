<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_templates', function (Blueprint $table) {
            $table->foreignUuid('set_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('template_id')->constrained()->onDelete('cascade');
            $table->primary(['set_id', 'template_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_templates');
    }
};
