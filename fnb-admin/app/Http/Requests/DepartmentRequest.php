<?php

namespace App\Http\Requests;

use App\Helpers\AppHelper;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Department;

class DepartmentRequest extends FormRequest
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
        $department = Department::find($id);
        if (!empty($department)){
            if ($department->code != $this->input('code')){
                return [
                    'code' => 'required|unique:tbl_department,code',
                    'name' => 'required',
                ];
            } else {
                return [
                    'code' => 'required',
                    'name' => 'required',
                ];
            }
        } else {
            return [
                'code' => 'required|unique:tbl_department,code',
                'name' => 'required',
            ];
        }
    }
    public function messages()
    {
        return [
            'code.required' => lang('dt_code_required'),
            'code.unique' => lang('dt_code_unique'),
            'name.required' => lang('dt_name_required'),
        ];
    }
}
