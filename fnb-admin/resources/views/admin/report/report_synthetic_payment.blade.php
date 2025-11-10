@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/report/report_synthetic_payment">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <label for="partner_search">Người đại diện</label>
                        <select class="partner_search select2" id="partner_search"
                                data-placeholder="Chọn ..." name="partner_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="representative_search">Cơ sở kinh doanh</label>
                        <select class="representative_search select2" id="representative_search"
                                data-placeholder="Chọn ..." name="representative_search">
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="month_search">Tháng</label>
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
                    <div class="col-md-2">
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
                    <div class="col-md-12 m-t-10">
                        <button id="exportButton" class="btn btn-default">Xuất Excel</button>
                    </div>
                </div>
                <table id="table_report_synthetic_payment" class="table table-bordered table_report_synthetic_payment">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Tháng/Năm')}}</th>
                        <th class="text-center">{{lang('Người đại diện')}}</th>
                        <th class="text-center">{{lang('Cơ sở kinh doanh')}}</th>
                        <th class="text-center">{{lang('Thành viên')}}</th>
                        <th class="text-center">{{lang('Tổng số người giới thiệu (Fn)')}}</th>
                        <th class="text-center">{{lang('Tổng chi tiêu')}}</th>
                        <th class="text-center">{{lang('Tiền HH đối tác')}}</th>
                        <th class="text-center">{{lang('Tiền HH thành viên')}}</th>
                        <th class="text-center">{{lang('Khu vực kinh doanh')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function (){
            searchAjaxSelect2('#partner_search','admin/category/searchCustomer',0,{type_client:2})
            searchAjaxSelect2('#representative_search','admin/category/searchRepresentativer')
            search_daterangepicker('date_search');
        })
        var fnserverparams = {
            'partner_search': '#partner_search',
            'representative_search': '#representative_search',
            'year_search': '#year_search',
            'month_search': '#month_search',
            'ares_search': '#ares_search',
        };
        var oTable;
        oTable = InitDataTable('#table_report_synthetic_payment', 'admin/report/getReportSyntheticPayment', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getReportSyntheticPayment",
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
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "50px"
                },
                {data: 'month_year', name: 'month_year',width: "100px", orderable: false },
                {data: 'partner', name: 'partner',orderable: false },
                {data: 'partner_representative', name: 'partner_representative',orderable: false },
                {data: 'customer', name: 'customer',width: "200px", orderable: false },
                {data: 'total_customer', name: 'total_customer',width: "200px", orderable: false },
                {data: 'total_payment', name: 'total_payment',width: "150px", orderable: false },
                {data: 'payment_partner', name: 'payment_partner',width: "80px", orderable: false},
                {data: 'payment_customer', name: 'payment_customer',width: "100px", orderable: false },
                {data: 'ares', name: 'ares',width: "100px", orderable: false},
            ],
        });
        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });

        var table = $('#table_report_synthetic_payment').DataTable();

        // Sự kiện click để xuất Excel
        $('#exportButton').click(function() {
            // Lấy tiêu đề cột từ DataTable
            var tableData = [];
            var header = [];

            // Lấy tên các cột từ bảng tiêu đề
            $('#table_report_synthetic_payment thead tr th').each(function() {
                header.push($(this).text());
            });

            tableData.push(header); // Thêm tiêu đề vào mảng dữ liệu

            // Lấy dữ liệu từ từng dòng
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                var rowData = [];
                // Lấy dữ liệu từ mỗi cột hiển thị
                $('#table_report_synthetic_payment thead tr th').each(function(index) {
                    var cellData = table.cell(rowIdx, index).node();
                    var cellText = $(cellData).find('a').text();
                    var cellTextDiv = $(cellData).find('div').first().find('div').text();
                    if (cellText) {
                        rowData.push(cellText); // Nếu có thẻ <a>, lấy văn bản từ đó
                    } else if($(cellData).find('div').text()){
                        if(cellTextDiv){
                            if($(cellData).find('div.text-right').first().find('div').text()){
                                if(!Number(intVal($(cellData).find('div.text-right').first().find('div').text()))){
                                    rowData.push(0)
                                } else {
                                    rowData.push(intVal($(cellData).find('div.text-right').first().find('div').text()));
                                }
                            } else if($(cellData).find('div.text-right').first().find('div').text()){
                                if(!Number(intVal($(cellData).find('div.text-right').first().find('div').text()))){
                                    rowData.push(0);
                                } else {
                                    rowData.push(intVal($(cellData).find('div.text-right').first().find('div').text()));
                                }
                            } else {
                                rowData.push(cellTextDiv);
                            }
                        } else {
                            rowData.push($(cellData).find('div').text());
                        }
                    } else {
                        rowData.push($(cellData).find('div').text()); // Nếu không có thẻ <a> và <div>, lấy dữ liệu gốc
                    }
                });

                tableData.push(rowData);
            });
            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.aoa_to_sheet(tableData);

            var colWidths = [];
            tableData.forEach(function(row) {
                row.forEach(function(cell, colIdx) {
                    const cellValue = String(cell);
                    if (!colWidths[colIdx]) {
                        colWidths[colIdx] = 0;
                    }
                    // Tính chiều rộng của cột dựa trên độ dài của nội dung
                    colWidths[colIdx] = Math.max(colWidths[colIdx], cellValue.length);
                });
            });

            // Đặt chiều rộng cột cho mỗi cột trong worksheet
            ws['!cols'] = colWidths.map(function(width) {
                return { wch: width }; // wch: width in characters
            });

            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

            XLSX.writeFile(wb, "bao_cao_tong_quan_chi_tieu.xlsx");
        });
    </script>
@endsection
