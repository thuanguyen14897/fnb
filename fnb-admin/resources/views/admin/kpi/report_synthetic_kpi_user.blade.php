@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a class="btn btn-default dropdown-toggle waves-effect waves-light" href="admin/kpi/detail_report_synthetic_kpi_user?type={{$type}}">{{lang('dt_create')}}</a>
                <a class="btn btn-danger dropdown-toggle waves-effect waves-light" style="margin-left: 10px" onclick="deleteKPI(); return false;">{{lang('Xóa KPI')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/kpi/report_synthetic_kpi_user?type={{$type}}">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-3">
                        <label for="month_search">Tháng</label>
                        <input type="hidden" name="type" id="type" value="{{$type}}">
                        <select class="month_search select2" id="month_search"
                                data-placeholder="Chọn ..." name="month_search">
                            @foreach(getMonth() as $key => $value)
                                <option {{date('m') == $key ? 'selected' : ''}} value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year_search">Năm</label>
                        <select class="year_search select2" id="year_search"
                                data-placeholder="Chọn ..." name="year_search">
                            @foreach(getYear() as $key => $value)
                                <option {{date('Y') == $key ? 'selected' : ''}} value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="staff_search">Nhân viên</label>
                        <select class="staff_search select2" id="staff_search"
                                data-placeholder="Chọn ..." name="staff_search">
                        </select>
                    </div>
                </div>
                <table id="table_report_synthetic_kpi_user" class="table table-bordered table-report_synthetic_kpi_user">
                    <thead>
                    <tr>
                        <th class="text-center"> <div class="checkbox mass_select_all_wrap text-center"><input
                                    type="checkbox" id="mass_select_all"
                                    data-to-table="report_synthetic_kpi_user"><label
                                    for="mass_select_all"></label></div></th>
                        <th class="text-center">{{lang('dt_image_user')}}</th>
                        <th class="text-center">{{lang('dt_code_user')}}</th>
                        <th class="text-center">{{lang('dt_name_user')}}</th>
                        <th class="text-center">{{lang('c_ares')}}</th>
                        <th class="text-center">{{lang('Danh số kinh doanh')}}</th>
                        <th class="text-center">{{lang('Chỉ số hợp đồng mới')}}</th>
                        <th class="text-center">{{lang('Duy trì khách hàng')}}</th>
                        <th class="text-center">{{lang('Vi phạm')}}</th>
                        <th class="text-center">{{lang('Điểm chuẩn hóa doanh số')}}</th>
                        <th class="text-center">{{lang('Điểm chuẩn hóa hợp đồng mới')}}</th>
                        <th class="text-center">{{lang('Điểm chuẩn hóa duy trì khách hàng')}}</th>
                        <th class="text-center">{{lang('Điểm chuẩn hóa vi phạm')}}</th>
                        <th class="text-center">{{lang('Tổng điểm KPI')}}</th>
                        <th class="text-center">{{lang('Xếp hạng KPI')}}</th>
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
            'staff_search': '#staff_search',
            'type': '#type',
        };
        $(function() {
            searchAjaxSelect2('#staff_search', 'api/category/getListStaff', 0, {select2: true})
        })
        $.each(fnserverparams, function(index, value) {
            $(value).change(function() {
                oTable.draw(false);
            })
        })
        oTable = InitDataTable('#table_report_synthetic_kpi_user', 'admin/kpi/getReportSyntheticKpiUser', {
            'order': [
                [3, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/kpi/getReportSyntheticKpiUser",
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
                        return `<div class="text-center">${row['calculate_kpi_detail_id']}</data>`;
                    },
                    data: 'id', name: 'id',width: "40px", orderable: false
                },
                {data: 'image', name: 'image',width: "120px" },
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'ares', name: 'ares'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'total_payment', name: 'total_payment'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'total_service', name: 'total_service'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'total_member', name: 'total_member'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'total_violate', name: 'total_violate'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'point_payment', name: 'point_payment'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'point_service', name: 'point_service'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'point_member', name: 'point_member'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'point_violation', name: 'point_violation'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'point_kpi', name: 'point_kpi'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-left">${data}</div>`;
                    },
                    data: 'name_kpi', name: 'name_kpi'
                },

            ]
        });

        function deleteKPI() {
            var ids = '';
            var rows = $('#table_report_synthetic_kpi_user').find('tbody tr');
            $.each(rows, function() {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids += checkbox.val() + ',';
                }
            });
            if (!ids) {
                alert('Xin vui lòng chọn nhân viên cần xóa KPI');
                return;
            }
            if (ids) {
                var r = confirm("{{lang('Bạn có chắc muốn xóa dữ liệu!')}}");
                if (r == false) {
                    oTable.draw('page');
                    return false;
                } else {
                    $.ajax({
                        url: 'admin/kpi/deleteKPI',
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            ids: ids,
                        },
                    })
                        .done(function(data) {
                            if (data.result) {
                                alert_float('success', data.message);
                                oTable.draw();
                            } else {
                                alert_float('danger', data.message);
                                oTable.draw('false');
                            }
                        })
                        .fail(function(data) {
                            alert_float('danger', 'errors');
                        })
                }
            }

        }
    </script>
@endsection
