<?php

namespace App\Http\Requests;

use App\Models\TypeCar;
use Illuminate\Foundation\Http\FormRequest;

class TypeCarRequest extends FormRequest
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
        $typeCar = TypeCar::find($id);
        if (!empty($typeCar)){
            return [
                'name' => 'required',
            ];
        } else {
            return [
                'name' => 'required',
                'image' => 'required',
            ];
        }
    }
    public function messages()
    {
        return [
            'name.required' => lang('dt_name_required'),
            'image.required' => lang('dt_image_required'),
        ];
    }
}
