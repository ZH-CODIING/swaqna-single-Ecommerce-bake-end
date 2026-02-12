<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.specs' => 'nullable',
            'coupon_code' => 'nullable|string',
            'zip_code' => 'required|string',
            'city' => 'required|string',
            'area' => 'required|string',
            'phone' => [
                'required',
                'string',
                'regex:/^966[0-9]{9}$/'
            ],
            'cod' => 'required|boolean',
            'shipping_gate' => 'required|string',
            'returnUrl' => 'nullable|string',
        ];
    }
}