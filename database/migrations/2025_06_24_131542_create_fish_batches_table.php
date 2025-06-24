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
        Schema::create('fish_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pond_id')->constrained('ponds');
            $table->foreignId('fish_type_id')->constrained('fish_types');
            $table->date('date_start');
            $table->integer('initial_count');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('fish_batches');
    }
};
