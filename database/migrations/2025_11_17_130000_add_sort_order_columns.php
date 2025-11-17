<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('collections') && !Schema::hasColumn('collections', 'sort_order')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('status');
            });
        }
        if (Schema::hasTable('sets') && !Schema::hasColumn('sets', 'sort_order')) {
            Schema::table('sets', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('name');
            });
        }
        if (Schema::hasTable('photos') && !Schema::hasColumn('photos', 'sort_order')) {
            Schema::table('photos', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->nullable()->after('caption');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('collections') && Schema::hasColumn('collections', 'sort_order')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
        if (Schema::hasTable('sets') && Schema::hasColumn('sets', 'sort_order')) {
            Schema::table('sets', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
        if (Schema::hasTable('photos') && Schema::hasColumn('photos', 'sort_order')) {
            Schema::table('photos', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
