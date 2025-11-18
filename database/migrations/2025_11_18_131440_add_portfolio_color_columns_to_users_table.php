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
            if (!Schema::hasColumn('users', 'portfolio_background_color')) {
                $table->string('portfolio_background_color', 20)->nullable()->after('portfolio_accent_color');
            }
            if (!Schema::hasColumn('users', 'portfolio_text_color')) {
                $table->string('portfolio_text_color', 20)->nullable()->after('portfolio_background_color');
            }
            if (!Schema::hasColumn('users', 'portfolio_heading_color')) {
                $table->string('portfolio_heading_color', 20)->nullable()->after('portfolio_text_color');
            }
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
            if (Schema::hasColumn('users', 'portfolio_background_color')) {
                $table->dropColumn('portfolio_background_color');
            }
            if (Schema::hasColumn('users', 'portfolio_text_color')) {
                $table->dropColumn('portfolio_text_color');
            }
            if (Schema::hasColumn('users', 'portfolio_heading_color')) {
                $table->dropColumn('portfolio_heading_color');
            }
        });
    }
};

