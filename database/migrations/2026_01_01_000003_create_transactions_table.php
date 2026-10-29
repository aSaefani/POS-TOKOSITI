<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('amount_paid');
            $table->unsignedBigInteger('change_amount')->default(0);
            $table->enum('payment_method', PaymentMethod::values())->default(PaymentMethod::Cash->value);
            $table->enum('status', TransactionStatus::values())->default(TransactionStatus::Completed->value)->index();
            $table->string('void_reason')->nullable();
            $table->timestamp('transacted_at')->index();
            $table->timestamps();

            $table->index(['cashier_id', 'transacted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
