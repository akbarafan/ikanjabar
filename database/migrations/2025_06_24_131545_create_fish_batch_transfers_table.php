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
        Schema::create('fish_batch_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_batch_id')->constrained('fish_batches');
            $table->foreignId('target_batch_id')->constrained('fish_batches');
            $table->integer('transferred_count');
            $table->date('date_transfer');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('fish_batch_transfers');
    }
};
