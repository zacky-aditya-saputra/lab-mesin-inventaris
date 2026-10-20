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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::table('loan_items', function (Blueprint $table) {
            $table->string('status', 30)->default('PENDING')->after('requested_quantity');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->integer('returned_good_qty')->default(0)->after('return_condition');
            $table->integer('returned_damaged_qty')->default(0)->after('returned_good_qty');
            $table->integer('returned_missing_qty')->default(0)->after('returned_damaged_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_items', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'rejection_reason',
                'returned_good_qty',
                'returned_damaged_qty',
                'returned_missing_qty',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
