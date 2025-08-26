@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default waves-effect waves-light" href="admin/role/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_role')}}</h4>
                <ol class="breadcrumb">
                    <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                    <li><a href="admin/role/list">{{lang('dt_role')}}</a></li>
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
            @if(session('error'))
                <div class="alert alert-danger">
                    {{session('error')}}
                </div>
            @endif
            <div class="card-box table-responsive">
                <table id="table_role" class="table table-bordered table_role">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_role')}}</th>
                        <th class="text-center">{{lang('dt_name_permission')}}</th>
                        <th class="text-center">{{lang('dt_actions')}}</th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script type=text/javascript>
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_role', 'admin/role/getRole', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/role/getRole",
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
                {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'name', name: 'name'},
                {data: 'permission', name: 'permission'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
