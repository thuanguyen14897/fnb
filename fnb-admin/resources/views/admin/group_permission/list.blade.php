@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal"
                   data-toggle="dropdown"
                   aria-expanded="false" href="admin/group_permission/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title">{{lang('dt_group_permission')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/group_permission/list">{{lang('dt_group_permission')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_group_permission" class="table table-bordered table_group_permission">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_name_group_permission')}}</th>
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
        oTable = InitDataTable('#table_group_permission', 'admin/group_permission/getGroupPermission', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/group_permission/getGroupPermission",
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
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex', width: "80px"
                },
                {data: 'name', name: 'name'},
                {data: 'options', name: 'options', orderable: false, searchable: false, width: "150px"},

            ]
        });
    </script>
@endsection
