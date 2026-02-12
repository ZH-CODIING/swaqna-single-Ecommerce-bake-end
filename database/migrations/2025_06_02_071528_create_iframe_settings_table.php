<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

 public function up()
{
    Schema::create('iframe_settings', function (Blueprint $table) {
        $table->id();
        $table->boolean('facebook')->default(false);
        $table->boolean('instagram')->default(false);
        $table->boolean('twitter')->default(false);
        $table->boolean('whatsapp')->default(false);
        $table->timestamps();
    });
}

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iframe_settings');
    }
};
