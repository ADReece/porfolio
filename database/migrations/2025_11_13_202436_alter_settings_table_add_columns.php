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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'key')) {
                $table->string('key')->unique()->after('id');
            }
            if (!Schema::hasColumn('settings', 'value')) {
                $table->text('value')->nullable()->after('key');
            }
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('string')->after('value');
            }
            if (!Schema::hasColumn('settings', 'group')) {
                $table->string('group')->default('general')->after('type');
            }
            if (!Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('group');
            }
        });

        // Seed defaults if not present
        if (!DB::table('settings')->where('key', 'default_currency')->exists()) {
            DB::table('settings')->insert([
                'key' => 'default_currency',
                'value' => 'GBP',
                'type' => 'string',
                'group' => 'currency',
                'description' => 'Default currency for pricing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (!DB::table('settings')->where('key', 'currency_symbol')->exists()) {
            DB::table('settings')->insert([
                'key' => 'currency_symbol',
                'value' => '£',
                'type' => 'string',
                'group' => 'currency',
                'description' => 'Currency symbol to display',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            // No destructive down to avoid data loss
        });
    }
};
