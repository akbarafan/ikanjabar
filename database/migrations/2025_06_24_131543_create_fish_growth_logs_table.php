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
        Schema::create('fish_growth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fish_batch_id')->constrained('fish_batches');
            $table->integer('week_number');
            $table->float('avg_weight_gram');
            $table->float('avg_length_cm');
            $table->date('date_recorded');
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
        Schema::dropIfExists('fish_growth_logs');
    }
};
