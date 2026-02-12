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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('resource_type')->index();
            $table->unsignedBigInteger('resource_id')->nullable()->index();
            $table->timestamp('visited_at')->useCurrent()->index();
            $table->index(['resource_type', 'resource_id', 'visited_at'], 'visits_type_id_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
