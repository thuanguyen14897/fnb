@extends('admin.layouts.index')
@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="btn-group pull-right m-t-15">
            <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
               aria-expanded="false" href="admin/module_noti/detail">{{lang('dt_create')}}</a>
        </div>
        <h4 class="page-title text-capitalize">{{$title}}</h4>
        <ol class="breadcrumb">
            <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
            <li><a href="admin/module_noti/list">{{$title}}</a></li>
            <li class="active">{{lang('dt_list')}}</li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="card-box table-responsive">
            <table id="table_module_noti" class="table table-bordered">
                <thead>
                <tr>
                    <th class="text-center">{{lang('dt_stt')}}</th>
                    <th class="text-center">Tên thông báo</th>
                    <th class="text-center">Mô tả ngắn</th>
                    <th class="text-center">Banner</th>
                    <th class="text-center">Loại người dùng</th>
                    <th class="text-center">Loại thông báo</th>
                    <th class="text-center">Ngày lặp lại/ ngày gửi</th>
                    <th class="text-center">Số lượng đã gửi</th>
                    <th class="text-center">Trạng thái</th>
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
        var fnserverparams = {};
        var oTable;
        oTable = InitDataTable('#table_module_noti', 'admin/module_noti/getModuleNoti', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/module_noti/getModuleNoti",
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
                {data: 'detail', name: 'detail',width: "150px"},
                {data: 'banner', name: 'banner',width: "250px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'type_user', name: 'type_user',width: '120px'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'type', name: 'type',width: '100px'},
                {data: 'repeat', name: 'repeat'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'quantity_send', name: 'quantity_send',width: "100px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'active', name: 'active',width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
