<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('client')->nullable();
            $table->enum('status', ['draft', 'sent', 'viewed', 'accepted', 'declined'])->default('draft');
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('views')->default(0);
            $table->string('avg_time_open')->nullable();
            $table->string('last_seen')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};