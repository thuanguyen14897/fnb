@extends('admin.layouts.index')
@section('content')
    <style>
        .card-box {
            min-height: 180px;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('c_title_partner')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/partner/list">{{lang('c_title_partner')}}</a></li>
                <li class="active">{{ lang('dt_view_partner') }}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs navtab-bg nav-justified">
                <li class="active">
                    <a href="#info" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Thông tin chung</span>
                        <span class="hidden-xs">Thông tin chung</span>
                    </a>
                </li>
                <li class="">
                    <a href="#representative" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs"></span>
                        <span class="hidden-xs">Thông tin người đại diện</span>
                    </a>
                </li>
                <li class="">
                    <a href="#service" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs"></span>
                        <span class="hidden-xs">Quản lý gian hàng</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card-box">
                                <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Thông tin tài
                                        khoản</b></h5>
                                <div class="contact-card" style="display: flex;flex-wrap: wrap">
                                    @php
                                        $src = !empty($client['avatar']) ? ($client['avatar']) : asset('admin/assets/images/users/avatar-1.jpg');
                                        $classesT = 'btn-danger';
                                        $contentT = 'Khách hàng';
                                        if($client['type_client'] == 2) {
                                            $classesT = 'btn-info';
                                            $contentT = 'Đối tác';
                                        }
                                        $str = "<a class='text-center btn btn-xs $classesT'>$contentT</a>";

                                        $classes = $client['active'] == 1 ? "btn-info" : "btn-danger";
                                        $content = $client['active'] == 1 ? "Hoạt động" : "Khoá";
                                        $strStatus = "<a class='dt-update text-center btn btn-xs $classes'>$content</a>";

                                    @endphp
                                    <div class="member-img"
                                         style="display: flex;flex-direction: column;align-items: center">
                                        <a href="{{$src}}" data-lightbox="customer-profile"
                                           class="display-block mbot5 pull-left">
                                            <img class="img-circle" src="{{$src}}" alt=""
                                                 style="width: 100px;height: 100px">
                                        </a>
                                        <h4 class="m-t-0 m-b-5"><b>{{$client['fullname']}}</b></h4>
                                    </div>
                                    <div class="member-info-new">
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_phone_user')}}: </span><span>{{$client['phone']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_email_user')}}: </span><span>{{$client['email']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail"><span>{{lang('dt_date_created_customer')}}: </span><span>{{_dt($client['created_at'])}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Tỉnh/ Thành phố : </span><span>{{$client['address']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Phường/ Thị Trấn/ Xã : </span><span>{{$client['address']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Địa chỉ : </span><span>{{$client['address']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('c_active_client')}}: </span><span>{!! $strStatus !!}</span>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="representative">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-box">
                                <div class="title_business">
                                    <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Thông tin người đại diện</b></h5>
                                    <div class="edit_business edit_button">Chỉnh sửa <i style="margin-left: 5px"
                                                                                        class="fa fa-pencil"></i></div>
                                </div>
                                <form id="business_form"
                                      action="admin/partner/detailRepresentativePartner/{{$client['id']}}" method="post">
                                    <div class="row">
                                        <div class="result_business">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <input type="hidden" name="id"
                                                           class="id"
                                                           value="{{!empty($client['representative']) ? $client['representative']['id'] : 0}}">
                                                    <label
                                                        for="name_representative">Người đại diện </label>
                                                    <input type="text" name="name_representative" autocomplete="off"
                                                           readonly
                                                           value="{{!empty($client['representative']) ? $client['representative']['name'] : ''}}"
                                                           class="form-control name_representative">
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="email_representative">Email</label>
                                                            <input type="text" name="email_representative" autocomplete="off"
                                                                   value="{{!empty($client['representative']) ? $client['representative']['email'] : ''}}"
                                                                   readonly
                                                                   class="form-control email_representative">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="birthday_representative">Ngày tháng năm sinh</label>
                                                            <input type="text" name="birthday_representative" autocomplete="off"
                                                                   value="{{!empty($client['representative']) ? (!empty($client['representative']['birthday']) ? _dthuan($client['representative']['birthday']) : '') : ''}}"
                                                                   readonly
                                                                   class="form-control birthday_representative datepicker">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="phone_representative">Số điện thoại</label>
                                                            <input type="text" name="phone_representative" autocomplete="off"
                                                                   value="{{!empty($client['representative']) ? $client['representative']['phone'] : ''}}"
                                                                   readonly
                                                                   class="form-control phone_representative">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="mst_representative">Mã số thuế</label>
                                                            <input type="text" name="mst_representative" autocomplete="off"
                                                                   value="{{!empty($client['representative']) ? $client['representative']['mst'] : ''}}"
                                                                   readonly
                                                                   class="form-control mst_representative">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label
                                                        for="number_cccd_representative">Số CCCD </label>
                                                    <input type="text" name="number_cccd_representative" autocomplete="off"
                                                           readonly
                                                           value="{{!empty($client['representative']) ? $client['representative']['number_cccd'] : ''}}"
                                                           class="form-control number_cccd_representative">
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="date_cccd_representative">Ngày cấp</label>
                                                            <input type="text" name="date_cccd_representative" autocomplete="off"
                                                                   value="{{!empty($client['representative']) ? (!empty($client['representative']['date_cccd']) ? _dthuan($client['representative']['date_cccd']): '') : ''}}"
                                                                   readonly
                                                                   class="form-control date_cccd_representative datepicker">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label
                                                                for="date_end_cccd_representative">Ngày hết hạn</label>
                                                            <input type="text" name="date_end_cccd_representative" autocomplete="off"
                                                                   value="{{!empty($client['representative']) ? (!empty($client['representative']['date_end_cccd']) ? _dthuan($client['representative']['date_end_cccd']): '') : ''}}"
                                                                   readonly
                                                                   class="form-control date_end_cccd_representative datepicker">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label
                                                        for="issued_cccd_representative">Nơi cấp CCCD </label>
                                                    <input type="text" name="issued_cccd_representative" autocomplete="off"
                                                           readonly
                                                           value="{{!empty($client['representative']) ? $client['representative']['issued_cccd'] : ''}}"
                                                           class="form-control issued_cccd_representative">
                                                </div>
                                                <div class="form-group">
                                                    <label
                                                        for="type_representative">Loại hình kinh doanh</label>
                                                    <select class="form-control type_representative select2" name="type_representative"></select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="image_cccd">Hình CCCD</label>
                                                    <input type="file" name="image_cccd[]" multiple
                                                           id="image_business"
                                                           class="filestyle image_cccd">
                                                </div>
                                                <div class="form-group">
                                                    <label for="image_kd">Hình giấp phép kinh doanh</label>
                                                    <input type="file" name="image_kd[]" multiple
                                                           id="image_kd"
                                                           class="filestyle image_kd">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group div_image">
                                                    <label
                                                        for="client_business_image">{{lang('Hình ảnh CCCD')}}</label>
                                                    <div class="row">
                                                        @if(!empty($client['image_cccd']))
                                                            @foreach($client['image_cccd'] as $key => $value)
                                                                <div class="col-md-6">
                                                                    {!! loadImage($value['image_new'],'350px','img-rounded',$value['image'],true,'image_cccd_old','200px') !!}
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="form-group div_image">
                                                    <label
                                                        for="client_business_image">{{lang('Hình ảnh giấy phép')}}</label>
                                                    <div class="row">
                                                        @if(!empty($client['image_kd']))
                                                            @foreach($client['image_kd'] as $key => $value)
                                                                <div class="col-md-4">
                                                                    {!! loadImage($value['image_new'],'200px','img-rounded',$value['image'],true,'image_kd_old','300px') !!}
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button
                                                class="btn btn-default waves-effect waves-light pull-right save-business hide"
                                                type="submit">Lưu lại
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(".edit_business").click(function () {
            if ($(this).hasClass('active_new')) {
                $(this).removeClass('active_new');
                $("form#business_form").find('input').attr('readonly', 'readonly');
                $(".save-business").addClass('hide');
            } else {
                $("form#business_form").find('input').removeAttr('readonly');
                $(".save-business").removeClass('hide');
                $(this).addClass('active_new');
            }
        })

        $("#business_form").validate({
            rules: {
                name_representative: {
                    required: true,
                },
                email_representative: {
                    required: true,
                },
                birthday_representative: {
                    required: true,
                },
                phone_representative: {
                    required: true,
                },
                mst_representative: {
                    required: true,
                },
                number_cccd_representative: {
                    required: true,
                },
                date_end_cccd_representative: {
                    required: true,
                },
                issued_cccd_representative: {
                    required: true,
                },
            },
            messages: {
                name_representative: {
                    required: "{{lang('dt_required')}}",
                },
                email_representative: {
                    required: "{{lang('dt_required')}}",
                },
                birthday_representative: {
                    required: "{{lang('dt_required')}}",
                },
                phone_representative: {
                    required: "{{lang('dt_required')}}",
                },
                mst_representative: {
                    required: "{{lang('dt_required')}}",
                },
                number_cccd_representative: {
                    required: "{{lang('dt_required')}}",
                },
                date_end_cccd_representative: {
                    required: "{{lang('dt_required')}}",
                },
                issued_cccd_representative: {
                    required: "{{lang('dt_required')}}",
                },
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
                        } else {
                            $(".show_error").html(data.message);
                            alert_float('error', data.message);
                        }
                        $(".result_business").html(data.html);
                        initDatepicker();
                    })
                    .fail(function (err) {
                    });
                return false;
            }
        });
    </script>
@endsection
