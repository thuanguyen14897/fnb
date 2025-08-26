<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurchargeCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = !empty($this->input('id')) ? $this->input('id') : 0;
        return [
            'name' => 'required',
            'type' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => lang('dt_name_required'),
            'type.required' => 'Vui lòng chọn loại',
        ];
    }
}
