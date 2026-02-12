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
        Schema::create('payment_gates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('note')->nullable();
            $table->text('short_description')->nullable();
            $table->string('logo')->nullable();
            $table->json('faqs')->nullable();
            $table->json('meta_data')->nullable();
            $table->string('website')->nullable();
            $table->boolean('status')->default(true);
            $table->decimal('commission', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gates');
    }
};
