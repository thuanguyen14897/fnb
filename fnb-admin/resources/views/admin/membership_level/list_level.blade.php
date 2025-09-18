@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('Hạng thành viên')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/membership_level/list_level">{{lang('c_list_membership_level')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="row m-b-10">
                </div>
                <table id="table_membership_level" class="table table-bordered table_membership_level">
                    <thead>
                        <tr>
                            <th class="text-center">{{lang('STT')}}</th>
                            <th class="text-center">Icon</th>
                            <th class="text-center">Ảnh nền thẻ</th>
                            <th class="text-center">{{lang('Ảnh Header')}}</th>
                            <th class="text-center">{{lang('Tên')}}</th>
                            <th class="text-center">{{lang('Màu nền')}}</th>
                            <th class="text-center">{{lang('Màu chữ')}}</th>
                            <th class="text-center">{{lang('Màu nút')}}</th>
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
        };

        $(function() {
            oTable = InitDataTable('#table_membership_level', 'admin/membership_level/getListLevel', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': false,
                "ajax": {
                    "type": "POST",
                    "url": "admin/membership_level/getListLevel",
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
                        data: 'id', name: 'id',width: "80px"
                    },
                    {data: 'icon', name: 'icon',width: "90px",},
                    {data: 'image', name: 'image',width: "120px",},
                    {data: 'background_header', name: 'background_header',width: "120px"},
                    {data: 'name', name: 'name',width: "120px",},
                    {data: 'color_background', name: 'color_background',width: "120px"},
                    {data: 'color', name: 'color',width: "120px"},
                    {data: 'color_button', name: 'color_button',width: "120px"},
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

                ]
            })
            $.each(fnserverparams, function(filterIndex, filterItem) {
                $('' + filterItem).on('change', function() {
                    oTable.draw('page')
                });
            });
        })
    </script>
@endsection
