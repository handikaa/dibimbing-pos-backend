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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name', 150);
            $table->text('store_address')->nullable();
            $table->string('store_phone', 50)->nullable();
            $table->text('receipt_footer')->nullable();
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->string('logo_url', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
