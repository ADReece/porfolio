<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Collections: cover photo object position
        if (Schema::hasTable('collections') && !Schema::hasColumn('collections', 'cover_photo_object_position')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->string('cover_photo_object_position')->default('center center')->after('cover_photo_id');
            });
        }

        // Users: portfolio styling preferences
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'portfolio_font')) {
                    $table->string('portfolio_font')->nullable()->after('portfolio_display_mode');
                }
                if (!Schema::hasColumn('users', 'portfolio_accent_color')) {
                    $table->string('portfolio_accent_color', 20)->nullable()->after('portfolio_font');
                }
                if (!Schema::hasColumn('users', 'portfolio_theme')) {
                    $table->string('portfolio_theme')->nullable()->after('portfolio_accent_color'); // e.g. light, dark, auto
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('collections') && Schema::hasColumn('collections', 'cover_photo_object_position')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('cover_photo_object_position');
            });
        }
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'portfolio_font')) {
                    $table->dropColumn('portfolio_font');
                }
                if (Schema::hasColumn('users', 'portfolio_accent_color')) {
                    $table->dropColumn('portfolio_accent_color');
                }
                if (Schema::hasColumn('users', 'portfolio_theme')) {
                    $table->dropColumn('portfolio_theme');
                }
            });
        }
    }
};
