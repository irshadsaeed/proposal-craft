<?php

// ── 1. admin_settings ─────────────────────────────────────────
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Global platform settings (key-value store)
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general'); // general|mail|billing|security
            $table->string('type')->default('text');     // text|boolean|json|number
            $table->timestamps();
        });

        // Admin users (separate from regular users)
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('admin'); // admin|super_admin
            $table->string('avatar')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Platform revenue / Stripe transactions log
        Schema::create('admin_revenue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_payment_intent')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('plan_slug');               // free|pro|agency
            $table->string('billing_period');          // monthly|yearly
            $table->unsignedInteger('amount');         // cents
            $table->string('currency')->default('usd');
            $table->string('status');                  // succeeded|refunded|failed
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // Admin activity log
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('admin_users')->cascadeOnDelete();
            $table->string('action');                  // e.g. "user.suspended"
            $table->string('subject_type')->nullable();// e.g. "App\Models\User"
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('admin_revenue_logs');
        Schema::dropIfExists('admin_users');
        Schema::dropIfExists('admin_settings');
    }
};