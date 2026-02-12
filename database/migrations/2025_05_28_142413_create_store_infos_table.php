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
        Schema::create('store_info', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('logo');
            $table->string('subscription_package');
            $table->text('footer_text');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->string('color');
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->date('end_subscription_date')->nullable();
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('zip')->nullable();
            $table->string('store_status')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('start_subscription_date')->nullable();
            $table->string('subscription_duration')->nullable(); // لإستيعاب قيمة annual أو monthly
            $table->integer('location_id')->nullable(); // لأن الكود يحاول إرسال قيمة لهذا الحقل
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_infos');
    }
};
