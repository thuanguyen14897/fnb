@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/payment_mode/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_payment_mode')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/payment_mode/list">{{lang('dt_payment_mode')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <table id="table_payment_mode" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('dt_name_payment_mode')}}</th>
                        <th class="text-center">{{lang('dt_type')}}</th>
                        <th class="text-center">Số dư đầu kỳ</th>
                        <th class="text-center">{{lang('dt_note')}}</th>
                        <th class="text-center">{{lang('dt_status')}}</th>
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
        oTable = InitDataTable('#table_payment_mode', 'admin/payment_mode/getPaymentMode', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/payment_mode/getPaymentMode",
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
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'image', name: 'image',width: "150px"},
                {data: 'name', name: 'name',width: "200px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'type', name: 'type',width: "120px"},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-right">${data}</data>`;
                    },
                    data: 'balance', name: 'balance',width: "120px"
                },
                {data: 'note', name: 'note'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'active', name: 'active',width: "80px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
