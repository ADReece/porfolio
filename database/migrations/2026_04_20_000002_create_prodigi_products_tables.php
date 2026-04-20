<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prodigi_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('prodigi_product_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prodigi_product_id')->constrained('prodigi_products')->onDelete('cascade');
            $table->boolean('is_enabled')->default(true);
            $table->decimal('retail_price', 10, 2)->default(29.99);
            $table->timestamps();

            $table->unique(['user_id', 'prodigi_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodigi_product_user');
        Schema::dropIfExists('prodigi_products');
    }
};
