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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_coupon_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('total_price', 10, 2)->index();
            $table->string('status')->default('pending')->index();
            $table->string('shipping_gate')->nullable();
            $table->string('qr_path')->nullable();
            $table->text('address')->nullable();

            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('platfourm_shipping_price', 12, 2);
            $table->decimal('shippment_cost', 10, 2)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
