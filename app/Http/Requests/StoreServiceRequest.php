<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    // public function authorize(): bool
    // {
    //     return false;
    // }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'short_service_detail' => 'required|string|max:255',
            'description' => 'nullable|string',

            'steps' => 'required|array|min:1',

            'steps.*.title' => 'required|string|max:255',
            'steps.*.fields' => 'required|array|min:1',

            'steps.*.fields.*.label' => 'required|string|max:255',
            'steps.*.fields.*.document_key' => 'required|string|max:255',
            'steps.*.fields.*.type' => 'required|in:text,number,email,date,radio,textarea,select',
            'steps.*.fields.*.placeholder' => 'nullable|string|max:255',
            'steps.*.fields.*.required' => 'nullable|boolean',

            'steps.*.fields.*.options' => 'nullable|array',
        ];
    }
}
