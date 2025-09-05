@extends('admin.layouts.index')
@section('content')
    <style>
        .city-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }
        .districts {
            margin-top: 10px;
        }
        .tag {
            display: inline-block;
            background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
            color: white;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin: 3px 5px 3px 0;
            box-shadow: 0 2px 8px rgba(66, 165, 245, 0.3);
            transition: all 0.3s ease;
        }

        .tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.4);
        }

        .tag.district {
            background: linear-gradient(135deg, #2d8a31 0%, #008e06 100%);
            box-shadow: 0 2px 8px rgba(102, 187, 106, 0.3);
        }

        .tag.district:hover {
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.4);
        }
        .card-box {
            padding-bottom: 50px;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a type="button" class="btn btn-default dropdown-toggle waves-effect waves-light dt-modal" data-toggle="dropdown"
                   aria-expanded="false" href="admin/ares/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_ares')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/ares/list">{{lang('c_ares')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="province_search">{{lang('dt_province')}}</label>
                            <select class="select2" id="province_search"
                                    data-placeholder="Chọn ..." name="province_search"
                                    onchange="changeProvince(this)">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="ward_search">{{lang('dt_wards')}}</label>
                            <select class="select2" id="ward_search"
                                    data-placeholder="Chọn ..." name="ward_search">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <!--<div class="table-responsive">-->
                <div>

                    <table id="table_ares" class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('c_name_ares')}}</th>
                            <th class="text-center">{{lang('dt_province')}}</th>
                            <th class="text-center">{{lang('dt_user')}}</th>
                            <!--<th class="text-center">{{lang('c_ward')}}</th>-->
                            <th class="text-center">{{lang('dt_active')}}</th>
                            <th class="text-center">{{lang('dt_actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function() {
            searchAjaxSelect2('#province_search', 'api/category/getListProvince', 0,{
                'select2':true
            })
        })
        function changeProvince(_this) {
            var province_id = $(_this).val();
            searchAjaxSelect2Mutil(`#ward_search`,'api/category/getListWard',0,{
                'select2':true,
                province_id :province_id
            })
        }


        var fnserverparams = {
            'province_search': '#province_search',
            'ward_search': '#ward_search',
        };

        $.each(fnserverparams, function(index, value) {
            $(value).change(function() {
                oTable.draw(false);
            })
        })
        var oTable;
        oTable = InitDataTable('#table_ares', 'admin/ares/getList', {
            'order': [
                [0, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/ares/getList",
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
                    data: 'id', name: 'DT_RowIndex',width: "80px" },
                {data: 'name', name: 'name', orderable: false},
                {data: 'data_province', name: 'data_province', orderable: false},
                {data: 'data_user', name: 'data_user', orderable: false},
                {data: 'active', name: 'active',width: "100px" , orderable: false},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px"},

            ]
        });
    </script>
@endsection
