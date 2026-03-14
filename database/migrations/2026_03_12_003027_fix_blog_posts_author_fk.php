<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['author_id']);

            // Recreate foreign key to admin_users
            $table->foreign('author_id')
                  ->references('id')
                  ->on('admin_users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['author_id']);

            // Optionally restore old foreign key to client_users if needed
            $table->foreign('author_id')
                  ->references('id')
                  ->on('client_users')
                  ->onDelete('cascade');
        });
    }
};