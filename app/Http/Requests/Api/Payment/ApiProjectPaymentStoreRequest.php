<?php

namespace App\Http\Requests\Api\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiProjectPaymentStoreRequest extends FormRequest
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
    $method=($this->method=='1') ? true : false;
    return [
      'method' => 'required|'.Rule::in(['1', '2', '3']),
      'stripe_token' => Rule::requiredIf($method),
      'url_success' => 'nullable|string|url',
      'url_cancel' => 'nullable|string|url'
    ];
  }
}
