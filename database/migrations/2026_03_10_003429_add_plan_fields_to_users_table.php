<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_users', function (Blueprint $table) {
            $table->string('plan_slug')->default('free')->after('email');
            $table->boolean('is_active')->default(true)->after('plan_slug');
            $table->timestamp('last_active_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('client_users', function (Blueprint $table) {
            $table->dropColumn(['plan_slug', 'is_active', 'last_active_at']);
        });
    }
};
