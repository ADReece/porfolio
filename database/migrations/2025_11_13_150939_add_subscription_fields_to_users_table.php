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
        Schema::table('users', function (Blueprint $table) {
            // Note: stripe_id, pm_type, pm_last_four, and trial_ends_at are added by Cashier's migration
            $table->foreignId('subscription_plan_id')->nullable()->constrained();
            $table->string('stripe_connect_id')->nullable(); // For receiving payments via Stripe Connect
            $table->boolean('stripe_connect_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_plan_id');
            $table->dropColumn([
                'stripe_connect_id',
                'stripe_connect_enabled'
            ]);
        });
    }
};
