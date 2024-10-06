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
        Schema::create('photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->boolean('private')->default(0);
            $table->foreignUuid('user_id')->constrained('users');
            $table->string('caption')->nullable();
            $table->string('description')->nullable();
            $table->string('url');
            $table->string('tags');
            $table->bigInteger('size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('photos');
    }
};
