@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/kpi/report_synthetic_kpi_user?type="{{$type}}">{{$title}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <form id="kpiForm" action="admin/kpi/detail_report_synthetic_kpi_user" method="post" data-parsley-validate novalidate>
        {{csrf_field()}}
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
                        <?= lang('Nhân viên', 'staff_search') ?>
                        <input name="staff_search" id="staff_search" class="staff_search form-control"
                               placeholder="<?= lang('Tìm kiếm nhân viên') ?>" style="width: 100%;">
                    </div>
                    <div class="col-md-2" style="margin-top: 5px;">
                        <br>
                        <button type="submit" class="btn btn-primary add">
                            <?php echo lang('Lưu lại'); ?>
                        </button>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-success btn-filter">
                        <?php echo lang('Lọc'); ?>
                    </button>
                </div>
                <div class="col-md-12 view-table-synthetic-kpi-user" style="padding: 1px; margin-top: 10px">

                </div>
            </div>
        </div>
    </div>
    </form>
@endsection
@section('script')
    <script>
        function loadSyntheticKpiUser() {
            month_search = $('#month_search').val();
            year_search = $('#year_search').val();
            type = $('#type').val();

            if (month_search && year_search) {
                $.ajax({
                    type: "GET",
                    url: 'admin/kpi/load_add_report_synthetic_kpi_user',
                    data: {
                        month_search: month_search,
                        year_search: year_search,
                        type: type,
                    },
                    dataType: "html",
                    success: function (response) {
                        if (response) {
                            $('.view-table-synthetic-kpi-user').html(response);
                        }
                    }
                });
            }
        }
        loadSyntheticKpiUser();
        $(".btn-filter").click(function (){
            loadSyntheticKpiUser();
        })
        $("#kpiForm").validate({
            rules: {
            },
            messages: {
            },
            submitHandler: function (form) {
                var url = form.action;
                var form = $(form),
                    formData = new FormData(),
                    formParams = form.serializeArray();

                $.each(form.find('input[type="file"]'), function (i, tag) {
                    $.each($(tag)[0].files, function (i, file) {
                        formData.append(tag.name, file);
                    });
                });
                $.each(formParams, function (i, val) {
                    formData.append(val.name, val.value);
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                })
                    .done(function (data) {
                        if (data.result) {
                            alert_float('success', data.message);
                            window.location.href = 'admin/kpi/report_synthetic_kpi_user?type={{$type}}';
                        } else {
                            alert_float('error', data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        $(".show_error").html(htmlError);
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });

        $(document).ready(function () {
            searchTableCustomNew('.tb-synthetic-kpi-new', '#staff_search', '.tpagination', 1);
        });


        function tpanigationNew(elTable, pageCurrent, iCall = 0) {
            if (iCall == 0) {
                $('' + elTable + ' tbody tr').attr('tsearch', 'ok');
            }
            numberPage = 1000;
            $('' + elTable + ' tbody tr[tsearch="notok"]').css('display', 'none');
            $('' + elTable + ' tbody tr[tsearch="ok"]').css('display', 'block');
            sum = $('' + elTable + ' tbody tr[tsearch="ok"]').length;
            numPages = Math.ceil(sum / numberPage);
            start = (pageCurrent - 1) * numberPage;
            end = numberPage * pageCurrent - 1;
            listRows = $('' + elTable + ' tbody tr[tsearch="ok"]');
            for (i = 0; i < listRows.length; i++) {
                if (i >= start && i <= end) {
                    listRows[i].style.display = '';
                } else {
                    listRows[i].style.display = 'none';
                }
            }
            soNut = numPages;
        }

        function searchTableCustomNew(elTable, elSearch, elPanigation, type) {
            elTableNew = '.table';
            $(elSearch).keyup(function (event) {
                var search_string = bodauTiengViet($.trim($(elSearch).val()).replace(/ +/g, ' ').toLowerCase());
                if (search_string == '') {
                    $('' + elTable + ' tbody tr').attr('tsearch', 'ok');
                    tpanigationNew(elTable, 1, 1);
                    tpanigationNew(elTableNew, 1, 1);
                } else {
                    var listRows = $('' + elTable + ' tbody tr');
                    $(listRows).attr('tsearch', 'notok');
                    for (i = 0; i < listRows.length; i++) {
                        var str = bodauTiengViet($(listRows[i].children[2]).html().toLowerCase());
                        if (str.search(search_string) >= 0) {
                            $(listRows[i]).attr('tsearch', 'ok');
                        }
                    }
                    var listRowsNew = $('' + elTableNew + ' tbody tr');
                    $(listRowsNew).attr('tsearch', 'notok');
                    for (i = 0; i < listRowsNew.length; i++) {
                        var str = bodauTiengViet($(listRowsNew[i].children[2]).html().toLowerCase());
                        if (str.search(search_string) >= 0) {
                            $(listRowsNew[i]).attr('tsearch', 'ok');
                        }
                    }
                    tpanigationNew(elTable, 1, 1);
                    tpanigationNew(elTableNew, 1, 1);
                }
            });
        }
    </script>
@endsection
