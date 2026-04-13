<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('subscription_plans', 'upload_logo')) {
                $table->boolean('upload_logo')->default(false)->after('custom_templates');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_plans', 'upload_logo')) {
                $table->dropColumn('upload_logo');
            }
        });
    }
};
