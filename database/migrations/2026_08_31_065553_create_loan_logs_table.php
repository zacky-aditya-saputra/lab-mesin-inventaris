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
        Schema::create('loan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 100);
            $table->string('previous_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['loan_request_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_logs');
    }
};
