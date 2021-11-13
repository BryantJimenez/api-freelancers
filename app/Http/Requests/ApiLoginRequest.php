<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiLoginRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $username=(is_null($this->email)) ? true : false;
        $email=(is_null($this->username)) ? true : false;
        return [
            'username' => Rule::requiredIf($username).'|string|min:4|max:20|exists:users,username',
            'email' => Rule::requiredIf($email).'|string|email|min:5|max:191|exists:users,email',
            'password' => 'required|string|min:8'
        ];
    }
}
