<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('masonry_columns')->default(4)->after('portfolio_display_mode');
            $table->integer('photos_per_page')->default(20)->after('masonry_columns');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['masonry_columns', 'photos_per_page']);
        });
    }
};

