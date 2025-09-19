<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled by auth middleware/policies
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'department_id' => 'sometimes|nullable|integer|exists:departments,id',
            'file'          => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg',
            // max is in kilobytes => 20 MB; tweak if needed
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'uploaded file',
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'   => 'The :attribute may not be greater than 20 MB.',
            'file.mimes' => 'Only PDF, Office docs, and images are allowed.',
        ];
    }
}
