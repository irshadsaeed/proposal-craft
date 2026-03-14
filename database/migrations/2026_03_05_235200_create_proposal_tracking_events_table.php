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
        Schema::create('proposal_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->unsignedInteger('section_id')->nullable();
            $table->float('value')->nullable();
            $table->string('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('tracked_at');
            $table->timestamps();

            $table->index(['proposal_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_tracking_events');
    }
};
