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
        Schema::table('orders', function (Blueprint $table) {
            // Prodigi order tracking
            $table->string('prodigi_order_id')->nullable()->index();
            $table->enum('prodigi_status', ['pending', 'received', 'validated', 'processing', 'shipped', 'cancelled', 'failed'])->nullable();
            $table->decimal('prodigi_charge', 10, 2)->nullable(); // What Prodigi charged us
            $table->timestamp('prodigi_submitted_at')->nullable();
            $table->timestamp('prodigi_shipped_at')->nullable();
            $table->string('prodigi_tracking_number')->nullable();
            $table->text('prodigi_error_message')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'prodigi_order_id',
                'prodigi_status',
                'prodigi_charge',
                'prodigi_submitted_at',
                'prodigi_shipped_at',
                'prodigi_tracking_number',
                'prodigi_error_message',
            ]);
        });
    }
};
