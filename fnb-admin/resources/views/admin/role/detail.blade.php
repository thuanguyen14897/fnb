@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('dt_role')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/role/list">{{lang('dt_role')}}</a></li>
                <li class="active">{{!empty($role) ? lang('dt_edit') : lang('dt_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card-box">
                    <form action="admin/role/submit/{{$id}}" method="post" data-parsley-validate novalidate>
                        {{csrf_field()}}
                        <div class="form-group">
                            <label for="name">{{lang('dt_name_role')}}</label>
                            <input type="text" name="name" parsley-trigger="change" required
                                   placeholder="{{lang('dt_name_role')}}"
                                   value="{{!empty($role) ? $role->name : old('name')}}" class="form-control name">
                        </div>
                        @if($errors->has('name'))
                            <div class="alert alert-danger">{{ $errors->first('name') }}</div>
                        @endif
                        <h4 class="page-title">Phân Quyền</h4>
                        <div class="form-group" style="margin-top: 5px;margin-bottom: 5px">
                            <div class="row">
                                @foreach($groupPermission as $key => $value)
                                    @if(count($value->permission) > 0)
                                        <div class="col-md-6" style="margin-top: 5px">
                                            <div style="border: 1px solid #eee;padding: 10px;min-height: 170px">
                                                <h4 class="page-title">{{$value->name}}</h4>
                                                <input type="hidden" name="group_permission[]" value="{{$value->id}}">
                                                <div class="row">
                                                    @foreach($value->permission as $k => $v)
                                                        <div class="col-md-3">
                                                            <div class="checkbox checkbox-pink">
                                                                <input id="checkbox_{{$v->id}}" type="checkbox"
                                                                       name="permission[{{$value->id}}][]"
                                                                       class="permission_{{$value->id}}"
                                                                       value="{{$v->id}}"
                                                                       @if(!empty($role) && in_array($v->id,$role->permission->pluck('id')->toArray())){
                                                                            {{'checked'}}
                                                                       }
                                                                       @endif
                                                                       data-parsley-multiple="groups"
                                                                       data-parsley-mincheck="2">
                                                                <label
                                                                    for="checkbox_{{$v->id}}"> {{lang($v->name)}}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div class="col-md-12 row">
                                                        <div class="col-md-3 pull-left">
                                                            <a class="btn btn-default pull-left"
                                                               onclick="checkAll(this,{{$value->id}})">{{lang('dt_select_all')}}</a>
                                                        </div>
                                                        <div class="col-md-3 pull-left">
                                                            <a class="btn btn-danger pull-left"
                                                               onclick="cancelAll(this,{{$value->id}})">{{lang('dt_cancel_all')}}</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group text-right m-b-0" style="margin-top: 10px">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">{{lang('dt_save')}}</button>
                            <button type="reset" class="btn btn-default waves-effect button waves-light m-l-5">
                                <a href="admin/role/list">Cancel</a>
                            </button>
                        </div>

                    </form>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        function checkAll(_this, group_permission_id) {
            $(".permission_" + group_permission_id).prop('checked', true);
        }

        function cancelAll(_this, group_permission_id) {
            $(".permission_" + group_permission_id).prop('checked', false);
        }
    </script>
@endsection
