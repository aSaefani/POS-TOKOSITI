<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PaymentMethod;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('pos.use') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount_paid' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ];
    }

    /** @return array{items: array<int, array{product_id:int, quantity:int}>, payment_method: PaymentMethod, amount_paid: int} */
    public function payload(): array
    {
        return [
            'items' => $this->validated('items'),
            'payment_method' => PaymentMethod::from($this->validated('payment_method')),
            'amount_paid' => (int) $this->validated('amount_paid'),
        ];
    }
}
