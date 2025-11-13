<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Photographer, Videographer
            $table->string('slug')->unique(); // free, photographer, videographer
            $table->string('stripe_price_id')->nullable(); // Stripe Price ID
            $table->string('stripe_product_id')->nullable(); // Stripe Product ID
            $table->decimal('price', 8, 2)->default(0); // Monthly price
            $table->integer('photo_limit')->nullable(); // null = unlimited
            $table->integer('collection_limit')->nullable(); // null = unlimited
            $table->boolean('private_collections')->default(false);
            $table->boolean('watermarking')->default(false);
            $table->boolean('selling')->default(false);
            $table->boolean('video_upload')->default(false);
            $table->boolean('custom_templates')->default(false);
            $table->text('features')->nullable(); // JSON array of features
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
};
