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
        Schema::create('admin_tokens', function (Blueprint $table) {
            $table->id();
            $table->tinyText('facebook_token')->nullable();
            $table->tinyText('instagram_token')->nullable();
            $table->tinyText('youtube_token')->nullable();
            $table->tinyText('whatsapp_token')->nullable();
            $table->tinyText('google_token')->nullable();
            $table->tinyText('snapchat_token')->nullable();
            $table->tinyText('tiktok_token')->nullable();
            $table->tinyText('facebook_page_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_tokens');
    }
};
