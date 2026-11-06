<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrmWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'deal' => ['required', 'array'],
            'deal.title' => ['required', 'string', 'max:255'],
            'deal.pipeline' => ['required', 'string', 'max:120'],
            'deal.stage' => ['required', 'string', 'max:120'],
            'deal.amount' => ['required', 'numeric', 'min:0'],
            'deal.currency' => ['required', 'string', 'size:3'],
            'deal.probability' => ['required', 'numeric', 'between:0,1'],
            'deal.expected_close_date' => ['nullable', 'date'],
            'deal.owner' => ['required', 'array'],
            'deal.owner.id' => ['required', 'string'],
            'deal.owner.name' => ['required', 'string'],
            'deal.owner.email' => ['required', 'email'],
            'deal.owner.department' => ['sometimes', 'nullable', 'string'],
            'deal.organization' => ['required', 'array'],
            'deal.organization.id' => ['required', 'string'],
            'deal.organization.name' => ['required', 'string'],
            'deal.organization.cnpj' => ['sometimes', 'nullable', 'string'],
            'deal.organization.industry' => ['sometimes', 'nullable', 'string'],
            'deal.organization.employee_count' => ['sometimes', 'nullable', 'integer'],
            'deal.organization.tier' => ['sometimes', 'nullable', 'string'],
            'deal.organization.address' => ['sometimes', 'array'],
            'deal.organization.address.*' => ['nullable', 'string'],
            'deal.primary_contact' => ['required', 'array'],
            'deal.primary_contact.id' => ['required', 'string'],
            'deal.primary_contact.email' => ['required', 'email'],
            'deal.primary_contact.first_name' => ['sometimes', 'nullable', 'string'],
            'deal.primary_contact.last_name' => ['sometimes', 'nullable', 'string'],
            'deal.primary_contact.title' => ['sometimes', 'nullable', 'string'],
            'deal.primary_contact.phone' => ['sometimes', 'nullable', 'string'],
            'deal.primary_contact.decision_role' => ['sometimes', 'nullable', 'string'],
            'deal.primary_contact.preferred_channel' => ['sometimes', 'nullable', 'string'],
            'deal.stakeholders' => ['sometimes', 'array'],
            'deal.stakeholders.*' => ['array'],
            'deal.stakeholders.*.id' => ['required', 'string'],
            'deal.stakeholders.*.name' => ['sometimes', 'nullable', 'string'],
            'deal.stakeholders.*.role' => ['sometimes', 'nullable', 'string'],
            'deal.stakeholders.*.email' => ['required', 'email'],
            'deal.stakeholders.*.influence_level' => ['sometimes', 'nullable', 'string'],
            'deal.stakeholders.*.sentiment' => ['sometimes', 'nullable', 'string'],
            'deal.stakeholders.*.notes' => ['sometimes', 'nullable', 'string'],
            'deal.line_items' => ['sometimes', 'array'],
            'deal.line_items.*' => ['array'],
            'deal.line_items.*.product_id' => ['sometimes', 'nullable', 'string'],
            'deal.line_items.*.sku' => ['sometimes', 'nullable', 'string'],
            'deal.line_items.*.name' => ['required', 'string'],
            'deal.line_items.*.quantity' => ['required', 'numeric', 'min:0'],
            'deal.line_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'deal.line_items.*.discount_percentage' => ['sometimes', 'numeric', 'between:0,100'],
            'deal.line_items.*.total_price' => ['required', 'numeric', 'min:0'],
            'deal.activities' => ['sometimes', 'array'],
            'deal.activities.*' => ['array'],
            'deal.activities.*.id' => ['sometimes', 'nullable', 'string'],
            'deal.activities.*.type' => ['required', 'string'],
            'deal.activities.*.subject' => ['required', 'string'],
            'deal.activities.*.timestamp' => ['sometimes', 'nullable', 'date'],
            'deal.activities.*.duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'deal.activities.*.participants' => ['sometimes', 'array'],
            'deal.activities.*.participants.*' => ['string'],
            'deal.activities.*.outcome' => ['sometimes', 'nullable', 'string'],
            'deal.activities.*.content_snippet' => ['sometimes', 'nullable', 'string'],
            'deal.custom_fields' => ['sometimes', 'array'],
            'deal.metadata' => ['sometimes', 'array'],
        ];
    }
}
