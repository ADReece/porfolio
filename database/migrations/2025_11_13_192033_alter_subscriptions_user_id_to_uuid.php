<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop index referencing user_id if exists
            try { DB::statement('ALTER TABLE subscriptions DROP INDEX subscriptions_user_id_stripe_status_index'); } catch (\Throwable $e) {}
        });

        // Change user_id to UUID
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('user_id')->change();
        });

        // Recreate index and add FK
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'stripe_status']);
            // Add FK to users(id)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop FK and index
            try { $table->dropForeign(['user_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['user_id', 'stripe_status']); } catch (\Throwable $e) {}
        });
        // Revert to bigint if needed
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->index(['user_id', 'stripe_status']);
        });
    }
};
