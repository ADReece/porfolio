<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->boolean('hide_from_portfolio')->default(false)->after('watermarked');
        });

        Schema::table('sets', function (Blueprint $table) {
            $table->boolean('hide_from_portfolio')->default(false)->after('name');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->boolean('hide_from_portfolio')->default(false)->after('purchased');
        });

        // Set hide_from_portfolio to true for existing private collections
        DB::table('collections')
            ->where('private', true)
            ->update(['hide_from_portfolio' => true]);
    }

    public function down()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('hide_from_portfolio');
        });

        Schema::table('sets', function (Blueprint $table) {
            $table->dropColumn('hide_from_portfolio');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn('hide_from_portfolio');
        });
    }
};

