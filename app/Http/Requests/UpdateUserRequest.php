<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
            'user_id'            => 'required',
            'name'               => 'required|string|min:5|max:255',
            'email'              => "required|string|email|unique:users,email,{$this->user_id },id",
            'address'            => 'required|string|min:5|max:255',
            'password'           => ['required', Password::defaults()],
            'photos'             => 'required|array',
            'photos.*'           => 'required|file|image|max:5000',
            'creditcard_type'    => 'required|string',
            'creditcard_number'  => 'required|string',
            'creditcard_name'    => 'required|string',
            'creditcard_expired' => 'required|string',
            'creditcard_cvv'     => 'required|string'
        ];
    }
}
