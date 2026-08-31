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
        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->restrictOnDelete();
            $table->integer('requested_quantity');
            $table->integer('approved_quantity')->nullable();
            $table->string('return_condition', 20)->nullable();
            $table->index(['loan_request_id', 'tool_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
