<?php

namespace App\Http\Requests;

use App\Models\GroupPermission;
use Illuminate\Foundation\Http\FormRequest;

class GroupPermissionRequest extends FormRequest
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
        $groupPermission = GroupPermission::find($id);
        if (!empty($groupPermission)){
            if ($groupPermission->name_display != $this->input('name_display')){
                return [
                    'name' => 'required|unique:tbl_group_permissions,name',
                ];
            } else {
                return [
                    'name' => 'required',
                ];
            }
        } else {
            return [
                'name' => 'required|unique:tbl_group_permissions,name',
            ];
        }
    }
    public function messages()
    {
        return [
            'name.required' => lang('dt_name_required'),
            'name.unique' => lang('dt_name_unique'),
        ];
    }
}
