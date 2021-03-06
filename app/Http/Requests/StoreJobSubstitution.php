<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreJobSubstitution.
 *
 * @package App\Http\Requests
 */
class StoreJobSubstitution extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('store-job-substitutions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user' => 'required|integer',
            'start_at' => 'required|date',
            'end_at' => 'sometimes|date|after:start_at'
        ];
    }
}
