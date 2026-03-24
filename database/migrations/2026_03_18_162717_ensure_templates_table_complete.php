<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: ensure_templates_table_complete
 *
 * Safely adds any missing columns to the templates table
 * without touching existing data.
 * 
 * Run with:
 *   php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {

            /* content — stores the blocks JSON array.
               Must be MEDIUMTEXT (up to 16 MB) not TEXT (64 KB limit).
               If the column already exists as TEXT, change it. */
            if (!Schema::hasColumn('templates', 'content')) {
                $table->mediumText('content')->nullable()->after('color');
            } else {
                /* Upgrade TEXT → MEDIUMTEXT if it was created as TEXT */
                $table->mediumText('content')->nullable()->change();
            }

            /* thumbnail — optional preview image URL, stored after autosave */
            if (!Schema::hasColumn('templates', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('content');
            }

            /* is_active — lets admins soft-disable library templates */
            if (!Schema::hasColumn('templates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('thumbnail');
            }

            /* blocks_count — denormalised cache for listing page performance */
            if (!Schema::hasColumn('templates', 'blocks_count')) {
                $table->unsignedSmallInteger('blocks_count')->default(0)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            /* Only drop columns we added in up() — never touch content/color/etc */
            $toDrop = ['thumbnail', 'is_active', 'blocks_count'];
            $existing = array_filter($toDrop, fn($col) => Schema::hasColumn('templates', $col));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};