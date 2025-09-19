<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        // File share permissions are enforced by policy in the controller.
        return true;
    }

    public function rules(): array
    {
        return [
            'shared_with' => 'required|integer|exists:users,id',
            'permission'  => ['required', Rule::in(['view','edit','download'])],
        ];
    }
}
