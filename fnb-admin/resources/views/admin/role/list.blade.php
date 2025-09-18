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
                    data: 'id', name: 'id',width: "80px" },
                {data: 'name', name: 'name'},
                {data: 'permission', name: 'permission'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
        $('#table_role tbody').on('click', 'td .rows-child', function() {
            var tr = $(this).closest('tr');
            var row = oTable.row(tr);
            if (row.child.isShown()) {
                $(this).removeClass('fa-caret-down');
                $(this).addClass('fa-caret-right');
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                $(this).removeClass('fa-caret-right');
                $(this).addClass('fa-caret-down');
                row.child(loadInfoData(row.data())).show();
                tr.addClass('shown');
            }
        });

        function loadInfoData(cData) {
            tr1 = `
            <tr class="success">
                <td class="bold" style="width: 250px;padding: 7px">Chức vụ</td>
                <td class="bold" style="padding: 7px">Tên quyền</td>
                <td class="bold" style="width: 100px;padding: 7px">Tác vụ</td>
             </tr>`;
            tb = `<table class="dt-table tnh-table table-bordered" style="width: 90% !important; float: right;">
                    <tbody>
                        ${tr1}
                        ${cData.child}
                    </tbody>
                </table>`;
            return tb;
        }
    </script>
@endsection
