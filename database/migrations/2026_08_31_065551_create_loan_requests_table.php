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
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number', 30)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('purpose')->nullable();
            $table->string('identity_card_path', 255);
            $table->string('status', 20)->default('PENDING');
            $table->text('admin_notes')->nullable();
            $table->index(['user_id', 'status', 'ticket_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
