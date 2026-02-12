<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracking_links', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('custom_keyword')->nullable();
            $table->date('added_date')->nullable();
            $table->integer('visits')->default(0);
            $table->double('earns')->default(0);
            $table->integer('purchases_count')->default(0);
            $table->string('url')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('coordinator_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_links');
    }
};
