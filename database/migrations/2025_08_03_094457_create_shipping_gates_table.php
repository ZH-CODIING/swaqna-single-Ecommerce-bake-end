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
        Schema::create('shipping_gates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('note')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable(); 
            $table->string('city');
            $table->string('area')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('second_price', 10, 2)->nullable();
            $table->decimal('trader_price', 10, 2);
            $table->decimal('trader_second_price', 10, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->decimal('cod_charge', 5, 2)->nullable();
            $table->decimal('kg_additional', 5, 2)->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_gates');
    }
};
