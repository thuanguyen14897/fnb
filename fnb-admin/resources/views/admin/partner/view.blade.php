@extends('admin.layouts.index')
@section('content')
    <style>
        .card-box {
            min-height: 180px;
        }
        .tree ul {
            padding-top: 20px;
            position: relative;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 1px solid #ccc;
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 1px solid #ccc;
        }

        .tree li:only-child::before, .tree li:only-child::after {
            display: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        .tree li:first-child::before, .tree li:last-child::after {
            border: 0 none;
        }

        .tree li:last-child::before {
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
        }

        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }
        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 1px solid #ccc;
            width: 0;
            height: 20px;
        }
        .irs-min, .irs-max {
            background: #EDCD68;
            color: white;
            font-size: 25px;
        }
        .irs-from, .irs-to, .irs-single{
            top: 18px;
            font-size: 15px;
            background: #EDCD68;
        }
        .irs-slider {
            width: 16px;
            height: 18px;
            top: 46px;
            background-position: 0 -120px;
        }
        .irs-line {
            height: 12px; top: 50px;
            background-color: #e2dede;
        }
        .irs-bar {
            height: 12px; top: 50px;
            background-position: 0 -60px;
            background-color: #EDCD68 !important;
            background-image: none;

        }
        .irs-bar-edge {
            background-color: #EDCD68 !important;
            background-image: none;
            top: 50px;
            height: 12px; width: 9px;
            background-position: 0 -90px;
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
            <ul class="nav nav-tabs navtab-bg nav-justified" id="tab_partner">
                <li class="active">
                    <a href="#info" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Thông tin chung</span>
                        <span class="hidden-xs">Thông tin chung</span>
                    </a>
                </li>
                <li class="">
                    <a href="#representative" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs"></span>
                        <span class="hidden-xs">Thông tin cơ sở kinh doanh</span>
                    </a>
                </li>
                <li class="">
                    <a href="#favourite" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs"></span>
                        <span class="hidden-xs">Gian hàng yêu thích</span>
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

                                        $strReferral = '';
                                        $referral_level = $client['referral_level'];
                                        if (!empty($referral_level)) {
                                            if ($referral_level['parent']['type_client'] == 1) {
                                                $strReferral = "<a class='text-center label label-danger' target='_blank' href='admin/clients/view/" . $referral_level['parent_id'] . "'>" . $referral_level['referral_code'] . "</a>";
                                            } else {
                                                $strReferral = "<a class='text-center label label-danger' target='_blank' href='admin/partner/view/" . $referral_level['parent_id'] . "'>" . $referral_level['referral_code'] . "</a>";
                                            }
                                        }
                                        $staff = $client['staff'] ?? null;
                                        $hmtlStaff = '';
                                         if (!empty($staff)){
                                            $dtImage = $staff['image'];
                                            $image = '<div style="display: flex;justify-content:center;margin-top: 5px"
                                             class="show_image">
                                            <img src="' . $dtImage . '" alt="avatar"
                                                 class="img-responsive img-circle"
                                                 style="width: 35px;height: 35px">

                                            </div>';
                                            $str = '<div style="margin-left: 5px;text-align: center">' . $staff['name'] .' ('.$staff['code'].')</div>';
                                            $hmtlStaff = '<div style="display: flex;align-items: center;flex-wrap: wrap;justify-content: center">'.$image.$str.'</div>';
                                         }
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
                                            <span>Tỉnh/ Thành phố : </span><span>{{!empty($client['province']) ? $client['province']['Type'].' '.$client['province']['Name'] : ''}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Phường/ Thị Trấn/ Xã : </span><span>{{!empty($client['wards']) ? $client['wards']['Name'] : ''}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Địa chỉ : </span><span>{{$client['address']}}</span>
                                        </p>
                                        <div class="text-dark member-info-detail" style="align-items: center">
                                            <span>Nhân viên phụ trách : </span><span>{!! $hmtlStaff !!}</span>
                                        </div>
                                        <p class="text-dark member-info-detail">
                                            <span>Mã giới thiệu : </span><span class="label label-default">{{$client['referral_code']}}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>Người giới thiệu : </span>{!! $strReferral !!}</span>
                                        </p>
                                        <p class="text-dark member-info-detail">
                                            <span>{{lang('c_active_client')}}: </span><span>{!! $strStatus !!}</span>
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card-box">
                                <div class="title_driving_liscense">
                                    <h5 class="text-muted text-uppercase m-t-0 m-b-20" style="color: black"><b>Sơ đồ
                                            nhánh các cấp</b></h5>
                                </div>
                                <div>Tổng số thành viên: {{$countMember}}</div>
                                <div class="hide">Tổng số cấp: {{$level}}</div>
                                <div class="tree" style="overflow-x: auto;overflow-y: hidden; white-space: nowrap;">
                                    @php
                                        $html = get_parent_id_referral_level_html($dataReferralLevel)
                                    @endphp
                                    {!! $html !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="representative">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card-box">
                                <div class="title_business" style="justify-content:flex-end">
                                    <h5 class="text-muted text-uppercase m-t-0 m-b-20 hide" style="color: black"><b>Thông tin cở sở kinh doanh</b></h5>
                                    <div class="edit_business edit_button">Chỉnh sửa <i style="margin-left: 5px"
                                                                                        class="fa fa-pencil"></i></div>
                                </div>
                                <form id="business_form"
                                      action="admin/partner/detailRepresentativePartner/{{$client['id']}}" method="post">
                                    <div class="row">
                                        <div class="result_business">
                                            <ul class="nav nav-tabs" id="tab_partner">
                                                <li class="active">
                                                    <a href="#info_representative" data-toggle="tab" aria-expanded="false">
                                                        <span class="visible-xs">Thông tin cở sở kinh doanh</span>
                                                        <span class="hidden-xs">Thông tin cở sở kinh doanh</span>
                                                    </a>
                                                </li>
                                                <li class="">
                                                    <a href="#info_bank" data-toggle="tab" aria-expanded="false">
                                                        <span class="visible-xs">Thông tin ngân hàng</span>
                                                        <span class="hidden-xs">Thông tin ngân hàng</span>
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div class="tab-pane active" id="info_representative">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="image">Hình ảnh thương hiệu</label>
                                                            <input type="file" name="image_avatar" id="image_avatar" class="filestyle image_avatar"
                                                                   data-buttonbefore="true">
                                                            @if(!empty($client['representative']) && $client['representative']['image'] != null)
                                                                <div style="display: flex;justify-content:center;margin-top: 5px"
                                                                     class="show_image">
                                                                    <img src="{{$client['representative']['image']}}" alt="image"
                                                                         class="img-responsive img-circle"
                                                                         style="width: 150px;height: 150px">
                                                                </div>
                                                            @else
                                                                <div style="display: flex;justify-content:center;margin-top: 5px"
                                                                     class="show_image">
                                                                    <img src="admin/assets/images/users/avatar-1.jpg" alt="image"
                                                                         class="img-responsive img-circle"
                                                                         style="width: 70px;height: 70px">

                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="form-group">
                                                            <input type="hidden" name="id"
                                                                   class="id"
                                                                   value="{{!empty($client['representative']) ? $client['representative']['id'] : 0}}">
                                                            <label
                                                                for="name_representative">Tên cơ sở kinh doanh </label>
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
                                                            <label for="staff_id_representative">{{lang('Nhân viên phụ trách')}}</label>
                                                            <select class="form-control" name="staff_id_representative" id="staff_id_representative" required style="width: 100%;height: 35px">
                                                                @if(!empty($client['staff']))
                                                                    <option value="{{$client['staff']['id']}}">{{$client['staff']['name'] }} ({{$client['staff']['code']}})</option>
                                                                @endif
                                                            </select>
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
                                                            <select class="form-control type_representative select2" name="type_representative">
                                                                @foreach(getListTypeBusiness() as $key => $value)
                                                                    <option {{!empty($client['representative']) ? ($client['representative']['type'] == $value['id'] ? 'selected' : '') : ''}} value="{{$value['id']}}">{{$value['name']}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="image_cccd">Hình CCCD mặt trước</label>
                                                            <input type="file" name="image_cccd_before"
                                                                   id="image_cccd_before"
                                                                   class="filestyle image_cccd_before">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="image_cccd">Hình CCCD mặt sau</label>
                                                            <input type="file" name="image_cccd_after"
                                                                   id="image_cccd_after"
                                                                   class="filestyle image_cccd_after">
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
                                                                            {!! loadImage($value['image_new'],'350px','img-rounded',$value['image'],false,'image_cccd_old','200px') !!}
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
                                                <div class="tab-pane" id="info_bank">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="account_image">Hình ảnh QR code</label>
                                                            <input type="file" name="account_image" id="account_image" class="filestyle account_image"
                                                                   data-buttonbefore="true">
                                                            @if(!empty($client['representative']) && $client['representative']['account_image'] != null)
                                                                <div style="display: flex;justify-content:center;margin-top: 5px"
                                                                     class="show_image">
                                                                    <a href="{{$client['representative']['account_image']}}" data-lightbox="customer-profile"
                                                                       class="display-block mbot5 pull-left">
                                                                        <img src="{{$client['representative']['account_image']}}" alt="image"
                                                                             class="img-responsive img-circle"
                                                                             style="width: 150px;height: 150px">
                                                                    </a>
                                                                </div>
                                                            @else
                                                                <div style="display: flex;justify-content:center;margin-top: 5px"
                                                                     class="show_image">
                                                                    <img src="admin/assets/images/users/avatar-1.jpg" alt="image"
                                                                         class="img-responsive img-circle"
                                                                         style="width: 70px;height: 70px">

                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="form-group">
                                                            <input type="hidden" name="id"
                                                                   class="id"
                                                                   value="{{!empty($client['representative']) ? $client['representative']['id'] : 0}}">
                                                            <label
                                                                for="account_bank_representative">Tên ngân hàng</label>
                                                            <input type="text" name="account_bank_representative" autocomplete="off"
                                                                   readonly
                                                                   value="{{!empty($client['representative']) ? $client['representative']['account_bank'] : ''}}"
                                                                   class="form-control account_bank_representative">
                                                        </div>
                                                        <div class="form-group">
                                                            <label
                                                                for="account_number_representative">Số tài khoản</label>
                                                            <input type="text" name="account_number_representative" autocomplete="off"
                                                                   readonly
                                                                   value="{{!empty($client['representative']) ? $client['representative']['account_number'] : ''}}"
                                                                   class="form-control account_number_representative">
                                                        </div>
                                                        <div class="form-group">
                                                            <label
                                                                for="account_name_representative">Tên người hưởng thụ </label>
                                                            <input type="text" name="account_name_representative" autocomplete="off"
                                                                   readonly
                                                                   value="{{!empty($client['representative']) ? $client['representative']['account_name'] : ''}}"
                                                                   class="form-control account_name_representative">
                                                        </div>
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
                <div class="tab-pane" id="favourite">
                    <div class="row">
                        <div class="row m-b-10">
                            <div class="col-md-2">
                                <label for="group_category_service_search_favourite">{{lang('Nhóm danh mục')}}</label>
                                <select class="group_category_service_search_favourite select2" id="group_category_service_search_favourite"
                                        data-placeholder="Chọn ..." name="group_category_service_search_favourite">
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="category_service_search_favourite">{{lang('Danh mục')}}</label>
                                <select class="category_service_search_favourite select2" id="category_service_search_favourite"
                                        data-placeholder="Chọn ..." name="category_service_search_favourite">
                                    <option></option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status_search_favourite">Trạng thái</label>
                                <select class="status_search_favourite select2" id="status_search_favourite"
                                        data-placeholder="Chọn ..." name="status_search_favourite">
                                    <option></option>
                                    <option value="-1">Tất cả</option>
                                    @foreach(getListStatusService() as $key => $value)
                                        <option value="{{$value['id']}}">{{$value['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="customer_search_favourite">Đối tác</label>
                                <select class="customer_search_favourite select2" id="customer_search_favourite"
                                        data-placeholder="Chọn ..." name="customer_search_favourite">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        {!! loadTableServiceFavourite() !!}
                    </div>
                </div>
                <div class="tab-pane" id="service">
                    <input type="hidden" name="customer_id" class="customer_id" id="customer_id"
                           value="{{$client['id']}}">
                    <div class="row">
                        @include('admin.service.search')
                        {!! loadTableService() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @include('admin.service.script_index_js')
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

        $(document).ready(function () {
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#tab_partner a[href="#' + activeTab + '"]').tab('show');
                localStorage.removeItem('activeTab'); // dùng xong thì xóa
            }
        });

        $(document).ready(function() {
            searchAjaxSelect2('#staff_id_representative', 'api/category/getListStaff', 0, {select2: true})
        });
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
                date_cccd_representative: {
                    required: true,
                },
                date_end_cccd_representative: {
                    required: true,
                },
                issued_cccd_representative: {
                    required: true,
                },
                staff_id_representative: {
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
                date_cccd_representative: {
                    required: "{{lang('dt_required')}}",
                },
                date_end_cccd_representative: {
                    required: "{{lang('dt_required')}}",
                },
                issued_cccd_representative: {
                    required: "{{lang('dt_required')}}",
                },
                staff_id_representative: {
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
                        localStorage.setItem('activeTab', 'representative');
                        location.reload();
                    })
                    .fail(function (err) {
                    });
                return false;
            }
        });
        var fnserverparamsNew = {
            'customer_favourite': '#customer_id',
            'group_category_service_search': '#group_category_service_search_favourite',
            'category_service_search': '#category_service_search_favourite',
            'status_search': '#status_search_favourite',
            'customer_search': '#customer_search_favourite',
        };
        var oTableFavourite;
        function loadTable() {
            oTableFavourite = InitDataTable('#table_service_favourite', 'admin/service/getList', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/service/getList",
                    "data": function (d) {
                        for (var key in fnserverparamsNew) {
                            d[key] = $(fnserverparamsNew[key]).val();
                        }
                        d['favourite'] = 1;
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {   "render": function (data, type, row) {
                            return `<div class="text-center">${data}</data>`;
                        },
                        data: 'id', name: 'id',width: "50px"
                    },
                    {data: 'image', name: 'image',width: "120px" , orderable: false},
                    {data: 'name', name: 'name',width: "250px" },
                    {data: 'province_id', name: 'province_id',width: "150px"},
                    {data: 'customer_id', name: 'customer_id',width: "150px"},
                    {data: 'group_category_service_id', name: 'group_category_service_id',width: "100px"},
                    {data: 'category_service_id', name: 'category_service_id',width: "100px"},
                    {data: 'price', name: 'price',width: "100px"},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active',width: "100px"},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'hot', name: 'hot',width: "100px"
                    },
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },
                ]
            });
        }
        $.each(fnserverparamsNew, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTableFavourite.draw('page')
            });
        });
        $(document).on('shown.bs.tab', 'a[href="#favourite"]', function () {
            loadTable();
        });
    </script>
@endsection
