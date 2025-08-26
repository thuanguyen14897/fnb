@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/other_amenities_service/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_other_amenities_car')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/other_amenities_service/list">{{lang('dt_other_amenities_car')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">

            <div class="card-box table-responsive">
                <table id="table_other_amenities" class="table table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('dt_image')}}</th>
                        <th class="text-center">{{lang('dt_name_other_amenities_car')}}</th>
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
        oTable = InitDataTable('#table_other_amenities', 'admin/other_amenities_service/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/other_amenities_service/getList",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    if(json.result == false){
                        alert_float('error',json.message);
                    }
                    return json.data;
                }
            },
            columnDefs: [
                {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "80px" },
                {data: 'image', name: 'image',width: "150px"},
                {data: 'name', name: 'name'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });
    </script>
@endsection
