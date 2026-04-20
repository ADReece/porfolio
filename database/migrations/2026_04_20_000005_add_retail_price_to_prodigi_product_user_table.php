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
        Schema::table('prodigi_product_user', function (Blueprint $table) {
            if (!Schema::hasColumn('prodigi_product_user', 'retail_price')) {
                $table->decimal('retail_price', 10, 2)->default(29.99)->after('is_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodigi_product_user', function (Blueprint $table) {
            if (Schema::hasColumn('prodigi_product_user', 'retail_price')) {
                $table->dropColumn('retail_price');
            }
        });
    }
};
