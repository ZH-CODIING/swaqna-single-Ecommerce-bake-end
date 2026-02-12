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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->json('images')->nullable();
            $table->string('img');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('code')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('weight', 10, 2)->default(1);
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->integer('discount')->nullable();
            $table->json('specs')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->integer('quantity');
            $table->boolean('isFeatured')->default(false);
            $table->date('discount_end_date')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->text('seo_keywords')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'brand_id']);
            $table->index('name');
            $table->index('isFeatured');
            $table->index(['category_id', 'isFeatured']);
            $table->index('price');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
