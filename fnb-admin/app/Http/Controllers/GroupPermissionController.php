<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupPermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GroupPermission;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class GroupPermissionController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        DB::enableQueryLog();
    }

    public function get_list()
    {

        return view('admin.group_permission.list');
    }

    public function getGroupPermission()
    {
        $groupPermission = GroupPermission::orderByRaw('id DESC')->get();
        return Datatables::of($groupPermission)
            ->addColumn('options', function ($groupPermission) {
                $edit = "<a class='dt-modal' href='admin/group_permission/detail/$groupPermission->id'><i class='fa fa-pencil'></i> " . lang('dt_edit_group_permission') . "</a>";
                $delete = '<a type="button" class="po-delete" data-container="body" data-html="true" data-toggle="popover" data-placement="left" data-content="
                    <button href=\'admin/group_permission/delete/' . $groupPermission->id . '\' class=\'btn btn-danger dt-delete\'>' . lang('dt_delete') . '</button>
                    <button class=\'btn btn-default po-close\'>' . lang('dt_close') . '</button>
                "><i class="fa fa-remove width-icon-actions"></i> ' . lang('dt_delete_group_permission') . '</a>';
                $options = ' <div class="dropdown text-center">
                            <button class="btn btn-default dropdown-toggle nav-link" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-expanded="true">
                             Tác vụ
                            <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                                <li style="cursor: pointer">' . $edit . '</li>
                                <li style="cursor: pointer">' . $delete . '</li>
                            </ul>
                        </div>';

                return $options;
            })
            ->addIndexColumn()
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->rawColumns(['options'])
            ->make(true);
    }

    public function get_detail($id = 0)
    {
        if (empty($id)) {
            $title = lang('dt_add_group_permission');
        } else {
            $title = lang('dt_edit_group_permission');
        }
        $groupPermission = GroupPermission::find($id);
        return view('admin.group_permission.detail', [
            'title' => $title,
            'id' => $id,
            'groupPermission' => $groupPermission,
        ]);
    }

    public function submit($id = 0, GroupPermissionRequest $groupPermissionRequest)
    {
        $data = [];
        if (!empty($id)) {
            $groupPermission = GroupPermission::find($id);
        }
        DB::beginTransaction();
        try {
            if (empty($id)) {
                $groupPermission = new GroupPermission();
            }
            $groupPermission->name = $groupPermissionRequest->name;
            $groupPermission->display_name = Str::slug($groupPermissionRequest->name);

            $groupPermission->save();

            DB::commit();
            if ($groupPermission) {
                $data['result'] = true;
                $data['message'] = lang('dt_success');
                return response()->json($data);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = lang('dt_error');
            return response()->json($data);
        }

    }

    public function delete($id)
    {
        $groupPermission = GroupPermission::find($id);
        try {
            if (count($groupPermission->permission) > 0) {
                $data['result'] = 0;
                $data['message'] = lang('dt_name_group_permission_exist');

                return response()->json($data);
            } else {
                $groupPermission->delete();
                $data['result'] = true;
                $data['message'] = lang('dt_success');

                return response()->json($data);
            }

        } catch (Exception $exception) {
            $data['result'] = false;
            $data['message'] = lang('dt_error');

            return response()->json($data);
        }
    }
}
