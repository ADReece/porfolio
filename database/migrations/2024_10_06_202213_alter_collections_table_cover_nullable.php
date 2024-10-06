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
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignUuid('cover_photo_id')->nullable()->after('event_date')->constrained('photos')->cascadeOnDelete();

            $table->dropForeign('collections_cover_image_id_foreign');
            $table->dropColumn('cover_image_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignUuid('cover_image_id')->after('event_date')->constrained('photos')->cascadeOnDelete();

            $table->dropForeign('collections_cover_photo_id_foreign');
            $table->dropColumn('cover_photo_id');
        });
    }
};
