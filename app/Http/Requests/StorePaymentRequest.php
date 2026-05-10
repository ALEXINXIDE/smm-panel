<?php

namespace App\Http\Requests;

use App\Models\FundAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StorePaymentRequest
 * ─────────────────────────────────────────────────────────────────────────────
 * Security changes:
 *  - `method` field removed — there are no hardcoded method names anymore.
 *    The method is inferred from the chosen fund_account.
 *  - `fund_account_id` is validated against the DB AND checked for is_active.
 *  - Amount limits come from config only (never hardcoded).
 * ─────────────────────────────────────────────────────────────────────────────
 */
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $minDeposit = config('services.payments.min_deposit', 100);
        $maxDeposit = config('services.payments.max_deposit', 500_000);

        return [
            // Must exist in fund_accounts AND be active.
            // Using Rule::exists with a where clause prevents submitting a
            // disabled account even if the attacker crafts a raw POST.
            'fund_account_id' => [
                'required',
                'integer',
                Rule::exists('fund_accounts', 'id')->where('is_active', true),
            ],

            'amount' => [
                'required',
                'numeric',
                "min:{$minDeposit}",
                "max:{$maxDeposit}",
            ],

            // Reference / transaction ID — alphanumeric + hyphens/underscores
            'reference' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9_\-]+$/',
            ],

            // Screenshot (optional but recommended)
            'screenshot' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        $min = config('services.payments.min_deposit', 100);
        $max = config('services.payments.max_deposit', 500_000);

        return [
            'fund_account_id.required' => 'Please select a payment account.',
            'fund_account_id.exists'   => 'The selected payment account is unavailable or has been disabled.',
            'amount.required'          => 'Please enter an amount.',
            'amount.numeric'           => 'Amount must be a valid number.',
            'amount.min'               => "Minimum deposit is PKR {$min}.",
            'amount.max'               => "Maximum deposit is PKR {$max}.",
            'reference.required'       => 'Please provide the transaction ID from your payment app.',
            'reference.max'            => 'Reference cannot exceed 100 characters.',
            'reference.regex'          => 'Reference may only contain letters, numbers, hyphens and underscores.',
            'screenshot.image'         => 'Screenshot must be a valid image file.',
            'screenshot.max'           => 'Screenshot may not be larger than 4 MB.',
        ];
    }
}
