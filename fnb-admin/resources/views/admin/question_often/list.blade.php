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
                   aria-expanded="false" href="admin/question_often/detail">{{lang('dt_create')}}</a>
            </div>
            <h4 class="page-title text-capitalize">{{lang('c_question_often')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/question_often/list">{{lang('c_question_often')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box table-responsive">
                <div class="checkbox checkbox-custom checkbox-inline m-b-10">
                    <input type="checkbox" id="active_order_by" value="1">
                    <label for="active_order_by">Bật sắp xếp câu hỏi</label>
                </div>
                <div class="clearfx"></div>
                <div>
                    <table id="table_question_often" class="table table-bordered sortableMain">
                        <thead>
                        <tr>
                            <th class="text-center">{{lang('dt_stt')}}</th>
                            <th class="text-center">{{lang('c_question')}}</th>
                            <th class="text-center">{{lang('c_question_reply')}}</th>
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
        oTable = InitDataTable('#table_question_often', 'admin/question_often/getList', {
            'order': [
                [0, 'asc    ']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/question_often/getList",
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
                    "render": function(data, type, row) {
                        return `<div class="text-center">${row['stt']}</div>
                                <span class="row_stt hide">${row['order_by']}</span>
                                <input class="inputStt" type="hidden" data-id="${row['id']}" value="${row['order_by']}">
                                `;
                    },
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: "80px"
                },
                {data: 'question', name: 'question',width: "250px", orderable: false},
                {data: 'content_reply', name: 'content_reply', orderable: false},
                {data: 'active', name: 'active',width: "100px" , orderable: false},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px"},
            ]
        });

        $('#active_order_by').change(function() {
            if($(this).prop('checked')) {
                $('.sortableMain tbody').sortable("enable");
            }
            else {
                $('.sortableMain tbody').sortable("disable");
            }
        })
        $('.sortableMain tbody').sortable({
            items: 'tr:not(.not-sortable)',
            distance: 20,
            start: function() {},
            stop: function() {
                EventUpdateSorMain(this);
            }
        });

        $('#active_order_by').trigger('change');



        function EventUpdateSorMain(_this) {
            if (confirm('Bạn có chắc muốn sắp xếp danh mục?')) {
                var inputStt = $('.inputStt');
                var limit = $('[name="table_question_often_length"]').val();
                var page = $('#table_question_often_paginate').find('.paginate_button.active').text();
                var nextStt = (limit * page) - limit + 1;
                $.each(inputStt, function(index, value) {
                    $(value).val((nextStt + index));
                    $(value).parent('td').find('.row_stt').text((nextStt + index));
                })
                var data = {};
                if (typeof(csrfData) !== 'undefined') {
                    data[csrfData['token_name']] = csrfData['hash'];
                }
                var list_order_by = {};
                $.each(inputStt, function(index, value) {
                    list_order_by[$(value).attr('data-id')] = $(value).val();
                })
                data['list_order_by'] = list_order_by;

                $.ajax({
                    type: "POST",
                    url: 'admin/question_often/order_by',
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.result) {
                            alert_float('success', response?.message);
                        } else {
                            alert_float('error', response?.message);
                        }
                        oTable.draw(false);
                    }
                });
            } else {
                oTable.draw(false);
            }
        }
    </script>
@endsection
