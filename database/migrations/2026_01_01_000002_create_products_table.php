<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 40)->unique();
            $table->string('barcode', 64)->nullable()->unique();
            $table->string('name', 150);
            $table->string('category', 80)->index();
            $table->unsignedBigInteger('price');          // dalam Rupiah (integer, hindari float)
            $table->unsignedBigInteger('cost')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_alert_threshold')->default(5);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
