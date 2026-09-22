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
            $table->foreignId('laboratory_id')->nullable()->constrained('laboratories')->nullOnDelete()->after('role');
            $table->string('identity_type', 20)->default('mahasiswa')->after('laboratory_id');
            $table->string('institution', 255)->nullable()->after('identity_type');
            $table->string('identity_card_path', 255)->nullable()->after('institution');
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->foreignId('laboratory_id')->nullable()->constrained('laboratories')->nullOnDelete()->after('category_id');
        });

        Schema::table('loan_requests', function (Blueprint $table) {
            $table->foreignId('laboratory_id')->nullable()->constrained('laboratories')->nullOnDelete()->after('user_id');
            $table->string('loan_letter_path', 255)->nullable()->after('identity_card_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laboratory_id');
            $table->dropColumn(['identity_type', 'institution', 'identity_card_path']);
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laboratory_id');
        });

        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laboratory_id');
            $table->dropColumn('loan_letter_path');
        });
    }
};
