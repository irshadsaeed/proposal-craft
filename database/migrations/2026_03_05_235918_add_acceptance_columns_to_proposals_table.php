<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Tracking
            $table->timestamp('first_viewed_at')->nullable()->after('status');
            $table->unsignedInteger('views_count')->default(0)->after('first_viewed_at');

            // Acceptance
            $table->string('accepted_by')->nullable()->after('views_count');
            $table->string('accepted_email')->nullable()->after('accepted_by');
            $table->timestamp('accepted_at')->nullable()->after('accepted_email');
            $table->string('accepted_ip', 45)->nullable()->after('accepted_at');
            $table->string('signature_path')->nullable()->after('accepted_ip');

            // Decline
            $table->timestamp('declined_at')->nullable()->after('signature_path');
            $table->text('decline_reason')->nullable()->after('declined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn([
                'first_viewed_at',
                'views_count',
                'accepted_by',
                'accepted_email',
                'accepted_at',
                'accepted_ip',
                'signature_path',
                'declined_at',
                'decline_reason',
            ]);
        });
    }
};
