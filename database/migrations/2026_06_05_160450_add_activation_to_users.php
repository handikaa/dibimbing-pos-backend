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
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('password');
            }

            if (! Schema::hasColumn('users', 'activation_token')) {
                $table->string('activation_token', 64)->nullable()->unique()->after('is_active');
            }

            if (! Schema::hasColumn('users', 'activation_token_expires_at')) {
                $table->timestamp('activation_token_expires_at')->nullable()->after('activation_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['activation_token']);

            $table->dropColumn([
                'is_active',
                'activation_token',
                'activation_token_expires_at',
            ]);
        });
    }
};
