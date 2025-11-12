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
            // First drop the old foreign key if it exists
            if (Schema::hasColumn('collections', 'cover_image_id')) {
                $table->dropForeign('collections_cover_image_id_foreign');
                $table->dropColumn('cover_image_id');
            }

            // Add new nullable cover_photo_id if it doesn't exist
            if (!Schema::hasColumn('collections', 'cover_photo_id')) {
                $table->foreignUuid('cover_photo_id')->nullable()->after('event_date')->constrained('photos')->nullOnDelete();
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
        Schema::table('collections', function (Blueprint $table) {
            $table->foreignUuid('cover_image_id')->after('event_date')->constrained('photos')->cascadeOnDelete();

            $table->dropForeign('collections_cover_photo_id_foreign');
            $table->dropColumn('cover_photo_id');
        });
    }
};
