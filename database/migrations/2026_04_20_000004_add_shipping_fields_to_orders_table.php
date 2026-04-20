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
        Schema::table('orders', function (Blueprint $table) {
            $table->text('customer_phone')->nullable()->after('customer_name');
            $table->text('shipping_address_line1')->nullable()->after('customer_phone');
            $table->text('shipping_address_line2')->nullable()->after('shipping_address_line1');
            $table->text('shipping_city')->nullable()->after('shipping_address_line2');
            $table->text('shipping_county')->nullable()->after('shipping_city');
            $table->text('shipping_postcode')->nullable()->after('shipping_county');
            $table->string('shipping_country_code', 2)->nullable()->after('shipping_postcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_phone',
                'shipping_address_line1',
                'shipping_address_line2',
                'shipping_city',
                'shipping_county',
                'shipping_postcode',
                'shipping_country_code',
            ]);
        });
    }
};
