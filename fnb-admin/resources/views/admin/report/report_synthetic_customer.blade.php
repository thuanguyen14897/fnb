@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/report/report_synthetic_customer">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                    <div class="col-md-2">
                        <input type="hidden" id="type_report" name="type_report" value="{{$type_report}}">
                        <label for="date_start_search">Ngày bắt đầu</label>
                        <input type="text" class="form-control datepicker" id="date_start_search" name="date_start_search"
                               autocomplete="off" value="{{date('01/m/Y')}}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_end_search">Ngày kết thúc</label>
                        <input type="text" class="form-control datepicker" id="date_end_search" name="date_end_search"
                               autocomplete="off" value="{{date('t/m/Y')}}">
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
                <table id="table_report_synthetic_customer" class="table table-bordered table_report_synthetic_customer">
                    <thead>
                    <tr>
                        <th class="text-center">{{lang('dt_stt')}}</th>
                        <th class="text-center">{{lang('Mã thành viên')}}</th>
                        <th class="text-center">{{lang('Họ và tên')}}</th>
                        <th class="text-center">{{lang('Hạng thành viên')}}</th>
                        <th class="text-center">{{lang('Số điện thoại')}}</th>
                        <th class="text-center">{{lang('Email')}}</th>
                        <th class="text-center">{{lang('Ngày đăng ký')}}</th>
                        <th class="text-center">{{lang('Ngày hết hạn sử dụng')}}</th>
                        <th class="text-center">{{lang('Khu vực kinh doanh')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
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
            search_daterangepicker('date_search');
        })
        var fnserverparams = {
            'date_start_search': '#date_start_search',
            'date_end_search': '#date_end_search',
            'ares_search': '#ares_search',
            'type_report': '#type_report',
        };
        var oTable;
        oTable = InitDataTable('#table_report_synthetic_customer', 'admin/report/getListSyntheticCustomer', {
            'order': [
                [0, 'asc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/report/getListSyntheticCustomer",
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
                {data: 'code', name: 'code',width: "150px", orderable: false },
                {data: 'fullname', name: 'fullname',orderable: false },
                {data: 'member_ship', name: 'member_ship',orderable: false },
                {data: 'phone', name: 'phone',width: "100px", orderable: false },
                {data: 'email', name: 'email',width: "120px", orderable: false },
                {data: 'created_at', name: 'created_at',width: "120px", orderable: false },
                {data: 'date_active', name: 'date_active',width: "120px", orderable: false },
                {data: 'ares', name: 'ares',width: "150px", orderable: false},
            ],
        });
        $.each(fnserverparams, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTable.draw('page')
            });
        });
        var table = $('#table_report_synthetic_customer').DataTable();

        // Sự kiện click để xuất Excel
        $('#exportButton').click(function() {
            // Lấy tiêu đề cột từ DataTable
            var tableData = [];
            var header = [];

            // Lấy tên các cột từ bảng tiêu đề
            $('#table_report_synthetic_customer thead tr th').each(function() {
                header.push($(this).text());
            });

            tableData.push(header); // Thêm tiêu đề vào mảng dữ liệu

            // Lấy dữ liệu từ từng dòng
            table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                var rowData = [];
                // Lấy dữ liệu từ mỗi cột hiển thị
                $('#table_report_synthetic_customer thead tr th').each(function(index) {
                    var cellData = table.cell(rowIdx, index).node();
                    var cellText = $(cellData).find('a').text();
                    var cellTextDiv = $(cellData).find('div').first().find('div').text();
                    if (cellText) {
                        rowData.push(cellText); // Nếu có thẻ <a>, lấy văn bản từ đó
                    } else if($(cellData).find('div').text()){
                        if(cellTextDiv){
                            if($(cellData).find('div.text-right').first().find('div').text()){
                                rowData.push(intVal($(cellData).find('div.text-right').first().find('div').text()));
                            } else if($(cellData).find('div.text-right').first().find('div').text()){
                                rowData.push(intVal($(cellData).find('div.text-right').first().find('div').text()));
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

            @if($type_report == 1)
            XLSX.writeFile(wb, "thong_ke_thanh_vien_hoat_dong.xlsx");
            @elseif($type_report == 2)
            XLSX.writeFile(wb, "thong_ke_thanh_vien_dang_khoa.xlsx");
            @else
            XLSX.writeFile(wb, "thong_ke_thanh_vien_gan_den_han_thanh_toan.xlsx");
            @endif
        });
    </script>
@endsection
