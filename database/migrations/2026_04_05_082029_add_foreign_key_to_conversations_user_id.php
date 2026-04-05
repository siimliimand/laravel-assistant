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
        Schema::table('conversations', function (Blueprint $table) {
            // First, update any existing null user_id values to prevent constraint violation
            // (In production, you'd want to handle this differently based on your data)
            \DB::statement('UPDATE conversations SET user_id = 1 WHERE user_id IS NULL');

            // Make user_id non-nullable
            $table->foreignId('user_id')->nullable(false)->change();

            // Add foreign key constraint with cascade delete
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['user_id']);

            // Make user_id nullable again
            $table->foreignId('user_id')->nullable()->change();
        });
    }
};
