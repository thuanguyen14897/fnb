@extends('admin.layouts.index')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{ lang('c_user') }}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{ lang('dt_index') }}</a></li>
                <li><a href="admin/user/list">{{ lang('c_user') }}</a></li>
                <li class="active">{{ $title }}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/user/action_import" method="post" id="userForm"
              data-parsley-validate novalidate enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">{{ lang('c_file_excel') }}</label>
                                    <input type="file" name="file" id="file" class="filestyle file" data-buttonbefore="true" accept=".xls,.xlsx,.csv">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <a target="_blank" href="upload/template/import_user.xlsx?1" class="btn btn-info m-t-30">
                                    <i class="fa fa-download" aria-hidden="true"></i> Download Template
                                </a>
                            </div>
                            <div class="clearfix"></div>
                            <div id="view_data_excel"></div>
                        </div>
                    </div>
                    <hr/>
                    <div class="form-group text-right m-b-0">
                        <button class="btn btn-primary waves-effect waves-light btn-save" type="submit">
                            {{ lang('c_import') }}
                        </button>
                        <button type="reset" class="btn btn-default waves-effect waves-light m-l-5">
                            {{ lang('dt_cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script>

        $("#userForm").validate({
            rules: {
                file: {
                    required: true,
                }
            },
            messages: {
                file: {
                    required: "{{ lang('dt_required') }}",
                }
            },
            invalidHandler: function(event, validator) {
                let errors = validator.numberOfInvalids();
                if (errors) {
                    let message = "";
                    validator.errorList.forEach(function(error) {
                        let fieldName = $(error.element).attr("id");
                        let label = $("label[for='" + fieldName + "']").text();
                        if (!label) {
                            fieldName = $(error.element).attr("name");
                            label = $("label[for='" + fieldName + "']").text();
                        }

                        message += `<div>${label}  ${error.message}</div>`;
                    });

                    if (!message) {
                        message = 'Bạn chưa nhập các trường';
                    }
                    alert_float('error', message, 5000);
                }
            },
            submitHandler: function(form) {
                $('.btn-save').attr('disabled', 'disabled');
                $('#view_data_excel').html('');
                var url = form.action;
                var form = $(form),
                    formData = new FormData(),
                    formParams = form.serializeArray();

                $.each(form.find('input[type="file"]'), function(i, tag) {
                    $.each($(tag)[0].files, function(i, file) {
                        formData.append(tag.name, file);
                    });
                });

                $.each(formParams, function(i, val) {
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
                    .done(function(data) {
                        if (data?.result) {
                            alert_float('success', data?.message);
                        } else {
                            alert_float('error', data?.message);
                        }
                        $('.btn-save').removeAttr('disabled', 'disabled');

                        var dataExcel = data?.data;
                        var TableExcel = $(`<table class="table"></table>`);
                        var HeaderExcel = $(`<thead></thead>`);
                        var tBodyExcel = $(`<tbody></tbody>`);
                        $.each(dataExcel, function(index, value) {
                            if(index == 0) {
                                var tr = $('<tr></tr>');
                                $.each(value, function(key, val) {
                                    console.log(val);
                                    tr.append(`<th>${val}</th>`);
                                });
                                console.log(tr);
                                HeaderExcel.append(tr);
                            } else {
                                var tr = $('<tr></tr>');
                                $.each(value, function(key, val) {
                                    tr.append(`<td>${val}</td>`);
                                });
                                console.log(tr);
                                tBodyExcel.append(tr);
                            }
                        });
                        TableExcel.append(HeaderExcel);
                        TableExcel.append(tBodyExcel);
                        $('#view_data_excel').append(TableExcel);
                    })
                    .fail(function(err) {
                        htmlError = '';
                        if (err?.responseJSON && err?.responseJSON?.errors) {
                            for (var [el, message] of Object?.entries(err.responseJSON?.errors)) {
                                htmlError += `<div>${message}</div>`;
                            }
                        } else {
                            htmlError = 'Error';
                        }

                        alert_float('error', htmlError);
                        $('.btn-save').removeAttr('disabled', 'disabled');
                    });
                return false;
            }
        });
    </script>
@endsection
