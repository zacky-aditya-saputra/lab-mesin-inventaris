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
        Schema::table('tools', function (Blueprint $table) {
            $table->integer('damaged_stock')->default(0)->after('available_stock');
        });

        Schema::table('loan_requests', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_notes');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('handed_over_at')->nullable()->after('approved_at');
            $table->timestamp('returned_at')->nullable()->after('handed_over_at');
            $table->timestamp('cancelled_at')->nullable()->after('returned_at');

            $table->string('borrower_name')->nullable()->after('cancelled_at');
            $table->string('borrower_identity_number', 50)->nullable()->after('borrower_name');
            $table->string('borrower_identity_type', 20)->nullable()->after('borrower_identity_number');
            $table->string('borrower_institution')->nullable()->after('borrower_identity_type');
            $table->string('borrower_phone', 30)->nullable()->after('borrower_institution');
        });

        Schema::table('loan_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('type', 30);
            $table->integer('delta');
            $table->integer('before_available');
            $table->integer('after_available');
            $table->integer('before_damaged');
            $table->integer('after_damaged');
            $table->text('reason');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');

        Schema::table('loan_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'approved_at',
                'handed_over_at',
                'returned_at',
                'cancelled_at',
                'borrower_name',
                'borrower_identity_number',
                'borrower_identity_type',
                'borrower_institution',
                'borrower_phone',
            ]);
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->dropColumn('damaged_stock');
        });
    }
};
