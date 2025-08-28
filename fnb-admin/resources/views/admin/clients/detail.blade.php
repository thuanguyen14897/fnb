@extends('admin.layouts.index')
@section('content')
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title">{{lang('c_title_client')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/clients/list">{{lang('c_title_client')}}</a></li>
                <li class="active">{{$title}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form action="admin/clients/detail" method="post" id="formClient" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#profile1" data-toggle="tab" aria-expanded="false">
                            <span class="visible-xs"><i class="fa fa-home"></i></span>
                            <span class="hidden-xs">Thông tin</span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="profile1">
                        <div class="card-box">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="hidden" name="id" value="{{$id}}">
                                            <label for="avatar">{{lang('c_avatar_client')}}</label>
                                            <input type="file" name="avatar" id="avatar" class="filestyle image"
                                                   data-buttonbefore="true">
                                            @if(!empty($client) && $client['avatar'] != null)
                                                <input type="hidden" name="image_old" id="image_old"
                                                       class="image_old"
                                                       data-buttonbefore="true" value="{{!empty($client) ? $client['avatar'] : ''}}">
                                                <div style="display: flex;justify-content:center;margin-top: 5px"
                                                     class="show_image">
                                                    <img src="{{asset($client['avatar'])}}" alt="image"
                                                         class="img-responsive img-circle"
                                                         style="width: 150px;height: 150px">
                                                </div>
                                            @else
                                                <div style="display: flex;justify-content:center;margin-top: 5px"
                                                     class="show_image">
                                                    <img src="admin/assets/images/users/avatar-1.jpg" alt="image"
                                                         class="img-responsive img-circle"
                                                         style="width: 150px;height: 150px">

                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="phone">{{lang('c_phone_client')}}</label>
                                            <input type="text" name="phone" id="phone" required autocomplete="off"
                                                   data-id="{{$id}}"
                                                   value="{{!empty($client) ? $client['phone'] : ''}}"
                                                   class="form-control phone">
                                        </div>
                                        <div class="form-group">
                                            <label for="fullname">{{lang('c_fullname_client')}}</label>
                                            <input type="text" name="fullname" id="fullname" required autocomplete="off"
                                                   value="{{!empty($client) ? $client['fullname'] : ''}}" class="form-control lastname">
                                        </div>
                                        <div class="form-group">
                                            <label for="email">{{lang('dt_email_user')}}</label>
                                            <input type="email" name="email" id="email" value="{{!empty($client) ? $client['email'] : ''}}" class="form-control email">
                                        </div>

                                        <div class="form-group">
                                            <label for="type_client">{{lang('c_type_client')}}</label>
                                            <select class="form-control" name="type_client" id="type_client" required style="width: 100%;height: 35px">
                                                <option value=""></option>
                                                <option value="1" {{!empty($client) && $client['type_client'] == 1 ? 'selected' : ''}}>Khách hàng</option>
                                                <option value="2" {{!empty($client) && $client['type_client'] == 2 ? 'selected' : ''}}>Đối tác</option>
                                            </select>
                                        </div>


                                        <div class="form-group" style="position: relative;">
                                            <label for="password">{{lang('dt_password_user')}}</label>
                                            <input type="password" class="form-control password" id="password" name="password">
                                            <a style="position: absolute; top:54%;right: 25px" href="javascript:;void(0)" ><i class="fa fa-eye"></i></a>
                                        </div>
                                        <div class="form-group">
                                            <label for="">{{lang('dt_active_user')}}</label>
                                            <div class="radio radio-custom radio-inline">
                                                <input type="radio"
                                                       @if(!empty($client) && $client['active'] == 1 )
                                                           checked
                                                       @else
                                                           checked
                                                       @endif
                                                       id="active1"
                                                       value="1" name="active">
                                                <label for="active1">Hoạt Động</label>
                                            </div>
                                            <div class="radio radio-custom radio-inline">
                                                <input type="radio"
                                                       @if(!empty($client) && $client['active'] == 0)
                                                           checked
                                                       @endif
                                                       id="active2"
                                                       value="0" name="active">
                                                <label for="active2"> Khoá </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="number_cccd">Số CCCD</label>
                                                <input type="text" name="number_cccd" id="number_cccd" value="{{!empty($client) ? $client['number_cccd'] : ''}}" class="form-control number_cccd">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_cccd">Ngày cấp CCCD</label>
                                                <input type="text" name="date_cccd" id="date_cccd" value="{{!empty($client['date_cccd']) ? _dthuan($client['date_cccd']) : ''}}" class="form-control datepicker date_cccd">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="issued_cccd">Nơi cấp CCCD</label>
                                                <input type="text" name="issued_cccd" id="issued_cccd" value="{{!empty($client) ? $client['issued_cccd'] : ''}}" class="form-control issued_cccd">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="number_passport">Số Passport</label>
                                                <input type="text" name="number_passport" id="number_passport" value="{{!empty($client) ? $client['number_passport'] : ''}}" class="form-control number_passport">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date_passport">Ngày cấp Passport</label>
                                                <input type="text" name="date_passport" id="date_passport" value="{{!empty($client->date_passport) ? _dthuan($client['date_passport']) : ''}}" class="form-control datepicker date_passport">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="issued_passport">Nơi cấp Passport</label>
                                                <input type="text" name="issued_passport" id="issued_passport" value="{{!empty($client) ? $client['issued_passport'] : ''}}" class="form-control issued_passport">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="province_id">{{lang('dt_province')}}</label>
                                                <select class="province_id select2" id="province_id"
                                                        data-placeholder="Chọn ..." name="province_id"
                                                        onchange="changeProvince(this)">
                                                    @if(!empty($client['province']))
                                                        <option value="{{$client['province']['Id']}}" {{!empty($client) && $client['province_id'] == $client['province']['Id'] ? 'selected' : ''}}>{{$client['province']['Name'] ?? ''}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="wards_id">{{lang('dt_wards')}}</label>
                                                <select class="wards_id select2" id="wards_id"
                                                        data-placeholder="Chọn ..." name="wards_id"
                                                        onchange="changeProvince(this)">
                                                    @if(!empty($client['wards']))
                                                        <option value="{{$client['wards']['Id']}}" {{!empty($client) && $client['wards_id'] == $client['wards']['Id'] ? 'selected' : ''}}>{{$client['wards']['Name'] ?? ''}}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="">Hạng mức hóa đơn</label>
                                                <div class="radio radio-custom radio-inline">
                                                    <input type="radio"
                                                           @if(!empty($client) && $client['active_limit_private'] == 1 )
                                                               checked
                                                           @else
                                                               checked
                                                           @endif
                                                           id="active_limit_private1"
                                                           value="1" name="active_limit_private">
                                                    <label for="active_limit_private1">Chiết khấu riêng</label>
                                                </div>
                                                <div class="radio radio-custom radio-inline">
                                                    <input type="radio"
                                                           @if(!empty($client) && $client['active_limit_private'] == 0)
                                                               checked
                                                           @endif
                                                           id="active_limit_private2"
                                                           value="0" name="active_limit_private">
                                                    <label for="active_limit_private2"> Chiết khấu theo hạng thành viên </label>
                                                </div>
                                            </div>
                                            <div class="form-group div-active_limit_private {{empty($client['active_limit_private']) ? 'hide' : ''}}">
                                                <label for="invoice_limit_private">Hạn mức hóa đơn mỗi lần thanh toá</label>
                                                <input type="text" name="invoice_limit_private" id="invoice_limit_private" value="{{!empty($client) ? $client['invoice_limit_private'] : ''}}" class="form-control invoice_limit_private">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right m-b-0">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    <script>
        $('input[name="active_limit_private"]').change(function() {
            if($(this).val() == 0) {
                $('.div-active_limit_private').addClass('hide');
            }
            else {
                $('.div-active_limit_private').removeClass('hide');
            }
        })

        var provinceNow = $('#province_id').val();
            $(document).ready(function(){

            searchAjaxSelect2('#province_id','api/category/getListProvince', 0,{
                'select2':true
            })
            $('#province_id').trigger('change');

            $(`#type_client`).select2();
            $(".form-group a").click(function(){
                var $this=$(this);
                if(!$this.hasClass('active')){
                    $this.parents(".form-group").find('input').attr('type','text')
                    $this.addClass('active');
                }else{
                    $this.parents(".form-group").find('input').attr('type','password')
                    $this.removeClass('active')
                }
            });

            $("#formClient").submit(function(e){
                if($('.form-group.error-or').length > 0) {
                    e.preventDefault();
                }
            });
        });

        function changeProvince(_this) {
            var province_id = $(_this).val();
            if(provinceNow != province_id) {
                $('#wards_id').val(0);
            }
            searchAjaxSelect2Mutil(`#wards_id`,'api/category/getListWard',0,{
                'select2':true,
                province_id :province_id
            })
            provinceNow = province_id;
        }

        $(".delete_image").click(function () {
            $(".show_image").addClass('hide');
            $(".image_old").val('');
        });

        $("#formClient").validate({
            rules: {
                phone: {
                    required: true,
                },
                fullname: {
                    required: true,
                }
            },
            messages: {
                phone: {
                    required: "{{lang('dt_required')}}",
                },
                fullname: {
                    required: "{{lang('dt_required')}}",
                }
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
                            alert_float('success',data.message);
                            window.location.href='admin/clients/list';
                        } else {
                            alert_float('error',data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [ el, message ] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        alert_float('error',htmlError);
                    });
                return false;
            }
        });
    </script>
@endsection
