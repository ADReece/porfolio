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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('annual_price', 8, 2)->nullable()->after('price');
            $table->string('annual_stripe_price_id')->nullable()->after('stripe_price_id');
            $table->unsignedTinyInteger('annual_discount_percent')->nullable()->after('annual_price');
            $table->boolean('recommended')->default(false)->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['annual_price', 'annual_stripe_price_id', 'annual_discount_percent', 'recommended']);
        });
    }
};
