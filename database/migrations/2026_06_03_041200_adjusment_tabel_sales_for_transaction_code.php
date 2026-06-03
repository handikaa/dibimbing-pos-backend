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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
            $table->string('table_code', 20)->nullable()->after('customer_phone');
            $table->string('order_code', 50)->unique()->after('table_code');
            $table->integer('daily_sequence')->default(1)->after('order_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['customer_phone', 'table_code', 'order_code', 'daily_sequence']);
        });
    }
};
