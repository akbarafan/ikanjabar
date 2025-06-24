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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fish_batch_id')->constrained('fish_batches');
            $table->date('date');
            $table->integer('quantity_fish');
            $table->float('avg_weight_per_fish_kg');
            $table->float('price_per_kg');
            $table->string('buyer_name', 100);
            $table->float('total_price');
            $table->string('documentation_file')->nullable();
            $table->uuid('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
