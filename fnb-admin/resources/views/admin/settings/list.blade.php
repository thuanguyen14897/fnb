@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_title_settings')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/settings?group=info">{{lang('c_title_settings')}}</a></li>
                <li class="active">{{$title}}</li>
            </ol>
        </div>
    </div>
    <style>
        .navbar-pills-flat {
            background: #fff;
            -webkit-box-shadow: 0 1px 15px 1px rgba(90, 90, 90, .08);
            box-shadow: 0 1px 15px 1px rgba(90, 90, 90, .08);
            padding-bottom: 0;
            margin-bottom: 25px;
            background: 0 0;
            border-radius: 1px;
            padding-left: 0;
            padding-right: 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }
        .navbar-pills.navbar-pills-flat.nav-tabs > li > a {
            border-bottom: 1px solid #e5e5e5;
            margin-right: 0;
            border-right: 1px solid #e5e5e5;
            border-left: 1px solid #e5e5e5;
            border-radius: 0;
        }
        .navbar-pills.navbar-pills-flat.nav-tabs > li:first-child > a {
            border-top: 1px solid #e5e5e5;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .mtop5 {
            margin-top: 5px;
        }
        .mtop10 {
            margin-top: 10px;
        }
        .mtop15 {
            margin-top: 15px;
        }
        .mtop20 {
            margin-top: 20px;
        }
        .mtop25 {
            margin-top: 25px;
        }
        .mtop30 {
            margin-top: 30px;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            @if(session('success'))
                <div class="alert alert-success">
                    {{session('success')}}
                </div>
            @endif
                <form action="admin/settings/submit/{{$group}}" method="post" id="formSettings" data-parsley-validate
                      novalidate
                      enctype="multipart/form-data">
                            {{csrf_field()}}
                    <div class="card-box">
                        <div class="panel-body">
                            <div class="col-md-3">
                                <h4>Danh mục cài đặt</h4>
                                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">
                                    <li class="{{(empty($group) || $group == 'info') ? 'active' : ''}}">
                                        <a href="admin/settings?group=info" data-group="info">
                                            {{lang('c_setting_info')}}
                                        </a>
                                    </li>
                                    <li class="{{(!empty($group) && $group == 'format_number') ? 'active' : ''}}">
                                        <a href="admin/settings?group=format_number" data-group="format_number">
                                            {{lang('c_setting_format_number')}}
                                        </a>
                                    </li>
                                    <li class="{{(!empty($group) && $group == 'info_contact') ? 'active' : ''}}">
                                        <a href="admin/settings?group=info_contact" data-group="web_contact">
                                            {{lang('c_info_contact')}}
                                        </a>
                                    </li>
                                    <li class="{{(!empty($group) && $group == 'other') ? 'active' : ''}}">
                                        <a href="admin/settings?group=other" data-group="other">
                                            {{lang('c_setting_other')}}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-9 mtop15">
                                <div class="panel_s">
                                    @include('admin.settings.' . $group)
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="btn-bottom-toolbar text-right mtop30">
                                <button type="submit" class="btn btn-default">
                                    {{lang('c_save_settings')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('.filestyle[type="file"]').change(function(e) {
            if($(this).prop('files').length > 0) {
                readURL(e.target, $(this).parent('div').find('img'));
            }
            else {
                imgDefault = $(this).parent('div').find('img').data('imgdefault');
                console.log(imgDefault);
                if(imgDefault != "" && imgDefault != undefined) {
                    $(this).parent('div').find('img').attr('src', imgDefault)
                }
            }
        })
        function readURL(input, thisData) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $(thisData).attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function changeTypeTransferAddress(_this){
            transfer_address = $(_this).val();
            $.ajax({
                url: 'admin/settings/changeTypeTransferAddress',
                type: 'GET',
                dataType: 'JSON',
                data: {
                    transfer_address: transfer_address,
                },
            })
                .done(function(data) {
                    if (data.result) {
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    location.reload();
                })
                .fail(function(data) {
                    alert_float('error', 'errors');
                    $(index).removeAttr('disabled');
                })
        }

    </script>

@endsection
