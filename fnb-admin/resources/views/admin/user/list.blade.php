@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light" href="admin/user/detail">{{lang('dt_create')}}</a>
                <a type="button" class="btn btn-info waves-effect waves-light m-l-10" href="admin/user/import_excel"><i class="fa fa-upload" aria-hidden="true"></i> Import Excel</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_user')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/user/list">{{lang('dt_user')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @if(session('success'))
                <div class="alert alert-success">
                    {{session('success')}}
                </div>
            @endif
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-3">
                        <label for="department_search">{{lang('dt_department')}}</label>
                        <select class="department_search select2" id="department_search"
                                data-placeholder="Chọn ..." name="department_search">
                            <option value="0">Tất cả</option>
                            @if(!empty($department))
                                @foreach($department as $key => $value)
                                    <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="role_search">{{lang('dt_role')}}</label>
                        <select class="role_search select2" id="role_search"
                                data-placeholder="Chọn ..." name="role_search">
                            <option value="0">Tất cả</option>
                            @if(!empty($role))
                                @foreach($role as $key => $value)
                                    <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="ares_search">{{lang('c_ares')}}</label>
                        <select class="ares_search select2" id="ares_search"
                                data-placeholder="Chọn ..." name="ares_search">
                            <option value="0">Tất cả</option>
                            @if(!empty($ares))
                                @foreach($ares as $key => $value)
                                    <option value="{{$value->id}}">{{$value->name}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <table id="table_user" class="table table-bordered table_user">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_image_user')}}</th>
                        <th class="text-center">{{lang('dt_code_user')}}</th>
                        <th class="text-center">{{lang('dt_name_user')}}</th>
                        <th class="text-center">{{lang('dt_email_user')}}</th>
                        <th class="text-center">{{lang('dt_phone_user')}}</th>
                        <th class="text-center">{{lang('dt_department')}}</th>
                        <th class="text-center">{{lang('dt_role')}}</th>
                        <th class="text-center">{{lang('c_ares')}}</th>
                        <th class="text-center">{{lang('dt_active_user')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var oTable;
        var fnserverparams = {
            "ares_search" : "#ares_search",
            "department_search" : "#department_search",
            "role_search" : "#role_search",
        };
        $.each(fnserverparams, function(index, value) {
            $(value).change(function() {
                oTable.draw(false);
            })
        })
        oTable = InitDataTable('#table_user', 'admin/user/getUsers', {
            'order': [
                [3, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/user/getUsers",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {data: 'image', name: 'image',width: "120px" },
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'phone', name: 'phone'},
                {data: 'department', name: 'department'},
                {data: 'role', name: 'role'},
                {data: 'ares', name: 'ares'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
