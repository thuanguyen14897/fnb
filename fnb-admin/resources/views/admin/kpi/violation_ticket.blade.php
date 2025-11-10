@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/kpi/detail_violation_ticket">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('dt_user')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/kpi/violation_ticket">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">

                </div>
                <table id="table_violation_ticket" class="table table-bordered table_violation_ticket">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('STT')}}</th>
                        <th class="text-center">{{lang('Mã phiếu')}}</th>
                        <th class="text-center">{{lang('Ngày tạo')}}</th>
                        <th class="text-center">{{lang('Nhân viên')}}</th>
                        <th class="text-center">{{lang('c_ares')}}</th>
                        <th class="text-center">{{lang('Nội dụng vi phạm')}}</th>
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
            'month_search': '#month_search',
            'year_search': '#year_search',
        };
        $.each(fnserverparams, function(index, value) {
            $(value).change(function() {
                oTable.draw(false);
            })
        })
        oTable = InitDataTable('#table_violation_ticket', 'admin/kpi/getViolationTicket', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/kpi/getViolationTicket",
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
                        return `<div class="text-center">${row['DT_RowIndex']}</data>`;
                    },
                    data: 'id', name: 'id',width: "80px", orderable: true
                },
                {data: 'reference_no', name: 'reference_no',width: "120px" },
                {data: 'date', name: 'date',width: "120px" },
                {data: 'staff_id', name: 'staff_id'},
                {data: 'ares', name: 'ares'},
                {data: 'note', name: 'note'},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },
            ]
        });
    </script>
@endsection
