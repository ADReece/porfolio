<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->boolean('watermarked')->default(false)->after('private');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->boolean('watermarked')->default(false)->after('private');
            $table->boolean('purchased')->default(false)->after('watermarked');
        });
    }

    public function down()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('watermarked');
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->dropColumn(['watermarked', 'purchased']);
        });
    }
};

