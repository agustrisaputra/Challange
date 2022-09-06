<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'q'  => 'nullable|string',
            'ob' => 'nullable|in:name,email',
            'sb' => 'nullable|in:asc,desc',
            'of' => 'nullable|numeric|min:0',
            'lt' => 'nullable|numeric|min:10'
        ];
    }
}
