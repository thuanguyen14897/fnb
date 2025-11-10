@extends('admin.layouts.index')
@section('content')
    <style>
        #map #infowindow-content {
            display: inline;
        }
    </style>
    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/service/list">{{$titleService}}</a></li>
                <li class="active">{{!empty($user) ? lang('dt_edit') : lang('dt_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <form id="serviceForm" action="admin/service/detail/{{$id}}" method="post" data-parsley-validate
              novalidate
              enctype="multipart/form-data">
            {{csrf_field()}}
            <div class="col-lg-12">
                <div class="card-box">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">{{lang('Tên dịch vụ')}}</label>
                                    <input type="text" name="name" parsley-trigger="change" id="name" autocomplete="off"
                                           value="{{!empty($dtData) ? $dtData['name'] : ''}}" class="form-control name">
                                </div>
                                <div class="form-group">
                                    <label for="image">{{ lang('dt_image') }}</label>
                                    <input type="file" name="image" id="image" data-input="false"
                                           class="filestyle image"
                                           data-buttonbefore="true">
                                    @if(!empty($dtData) && $dtData['image'] != null)
                                        @php
                                            $dtImage = !empty($dtData['image']) ? $dtData['image'] : null;
                                        @endphp
                                        {!! loadImageNew($dtImage,'110px','img-rounded','',false) !!}
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="image_store">{{ lang('Hình ảnh gian') }}</label>
                                    <input type="file" name="image_store[]" id="image_store" data-input="false" multiple
                                           class="filestyle image_store"
                                           data-buttonbefore="true">
                                    @if(!empty($dtData))
                                        <div style="display: flex;;flex-wrap: wrap">
                                            @if(!empty($dtData['image_store']))
                                                @foreach($dtData['image_store'] as $key => $value)
                                                    @php
                                                        $dtImage = !empty($value['image']) ? $value['image'] : null;
                                                    @endphp
                                                    {!! loadImage($dtImage,'120px','img-rounded',$value['name'],true,'image_store_old') !!}
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="image_menu">{{ lang('Hình ảnh menu') }}</label>
                                    <input type="file" name="image_menu[]" id="image_menu" data-input="false" multiple
                                           class="filestyle image_menu"
                                           data-buttonbefore="true">
                                    @if(!empty($dtData))
                                        <div style="display: flex;;flex-wrap: wrap">
                                            @if(!empty($dtData['image_menu']))
                                                @foreach($dtData['image_menu'] as $key => $value)
                                                    @php
                                                        $dtImage = !empty($value['image']) ? $value['image'] : null;
                                                    @endphp
                                                    {!! loadImage($dtImage,'120px','img-rounded',$value['name'],true,'image_menu_old') !!}
                                                @endforeach
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="group_category_service_id">{{lang('Nhóm danh mục')}}</label>
                                    <select class="group_category_service_id select2" id="group_category_service_id"
                                            data-placeholder="Chọn ..." name="group_category_service_id"
                                            onchange="changeGroupCategory(this)">
                                        <option></option>
                                        @if(!empty($dtData))
                                            @if(!empty($dtData['group_category_service']))
                                                <option value="{{$dtData['group_category_service']['id']}}"
                                                        selected>{{$dtData['group_category_service']['name']}}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="category_service_id">{{lang('Danh mục')}}</label>
                                    <select class="category_service_id select2" id="category_service_id"
                                            data-placeholder="Chọn ..." name="category_service_id">
                                        <option></option>
                                        @if(!empty($dtData))
                                            @if(!empty($dtData['category_service']))
                                                <option value="{{$dtData['category_service']['id']}}"
                                                        selected>{{$dtData['category_service']['name']}}</option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="customer_id">{{lang('dt_customer')}}</label>
                                    <select class="customer_id form-control select2" id="customer_id"
                                            data-placeholder="Chọn ..." name="customer_id">
                                        @if(!empty($dtData))
                                            @if(!empty($dtData['customer']))
                                                <option value="{{$dtData['customer']['id']}}"
                                                        selected>{{$dtData['customer']['fullname']}}
                                                    ({{$dtData['customer']['phone']}})
                                                </option>
                                            @endif
                                        @endif
                                    </select>

                                </div>
                                <div class="form-group">
                                    <label for="price">{{lang('Đơn giá')}}</label>
                                    <input type="text" name="price" parsley-trigger="change" id="price"
                                           onkeyup="formatNumBerKeyChange(this)" autocomplete="off"
                                           value="{{!empty($dtData) ? formatMoney($dtData['price']) : 0}}"
                                           class="form-control name">
                                </div>
                                <div class="form-group">
                                    <label for="html_percent">{{lang('Chương trình khuyến mãi')}}</label>
                                    <input type="text" name="html_percent" parsley-trigger="change" id="html_percent"
                                           autocomplete="off"
                                           value="{{!empty($dtData) ? ($dtData['html_percent']) : null}}"
                                           class="form-control html_percent">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="detail">{{lang('dt_detail')}}</label>
                                    <textarea cols="2" rows="3" class="form-control detail editor"
                                              name="detail">{!! !empty($dtData) ? htmlspecialchars_decode($dtData['detail']) : '' !!}</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="rules">{{lang('dt_rules')}}</label>
                                    <textarea cols="2" rows="3" class="form-control rules editor"
                                              name="rules">{{!empty($dtData) ? $dtData['rules'] : ''}}</textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="province_id">{{lang('dt_province')}}</label>
                                            <select class="province_id select2" id="province_id"
                                                    data-placeholder="Chọn ..." name="province_id"
                                                    onchange="changeProvince(this)">
                                                <option></option>
                                                @if(!empty($dtData))
                                                    @if(!empty($dtData['province']))
                                                        <option value="{{$dtData['province']['Id']}}"
                                                                selected>{{$dtData['province']['Name']}}</option>
                                                    @endif
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="wards_id">{{lang('dt_wards')}}</label>
                                            <select class="wards_id select2" id="wards_id"
                                                    data-placeholder="Chọn ..." name="wards_id">
                                                <option></option>
                                                @if(!empty($dtData))
                                                    @if(!empty($dtData['ward']))
                                                        <option value="{{$dtData['ward']['Id']}}"
                                                                selected>{{$dtData['ward']['Type']}} {{$dtData['ward']['Name']}}</option>
                                                    @endif
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="address">{{lang('dt_address')}}</label>
                                            <input type="text" id="address" name="address" parsley-trigger="change"
                                                   autocomplete="off"
                                                   value="{{!empty($dtData) ? $dtData['address'] : ''}}"
                                                   class="form-control address">
                                        </div>
                                        <input type="hidden" name="name_location" id="name_location"
                                               value="{{!empty($dtData) ? $dtData['name_location'] : ''}}">
                                        <input type="hidden" name="latitude" id="latitude"
                                               value="{{!empty($dtData) ? $dtData['latitude'] : ''}}">
                                        <input type="hidden" name="longitude" id="longitude"
                                               value="{{!empty($dtData) ? $dtData['longitude'] : ''}}">
                                        <div class="clearfix"></div>
                                        <div class="show_map">
                                            <div id="map" style="width:100%;height:200px;"></div>
                                            <div id="infowindow-content">
                                                <span id="place-name" class="title"></span><br/>
                                                <span id="place-address"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">{{lang('Số điện thoại')}}</label>
                                    <input type="text" name="phone_number" parsley-trigger="change"
                                           id="phone_number" autocomplete="off"
                                           value="{{!empty($dtData) ? $dtData['phone_number'] : ''}}"
                                           class="form-control phone_number">
                                </div>
                                <div class="form-group">
                                    <label for="fanpage_facebook">{{lang('Fanpage Facebook')}}</label>
                                    <input type="text" name="fanpage_facebook" parsley-trigger="change"
                                           id="fanpage_facebook" autocomplete="off"
                                           value="{{!empty($dtData) ? $dtData['fanpage_facebook'] : ''}}"
                                           class="form-control fanpage_facebook">
                                </div>
                                <div class="form-group">
                                    <label for="link_website">{{lang('Link website')}}</label>
                                    <input type="text" name="link_website" parsley-trigger="change" id="link_website"
                                           autocomplete="off"
                                           value="{{!empty($dtData) ? $dtData['link_website'] : ''}}"
                                           class="form-control link_website">
                                </div>
                                <div class="form-group">
                                    <label for="name_wifi">{{lang('Tên wifi')}}</label>
                                    <input type="text" name="name_wifi" parsley-trigger="change" id="name_wifi"
                                           autocomplete="off"
                                           value="{{!empty($dtData) ? $dtData['name_wifi'] : ''}}"
                                           class="form-control name_wifi">
                                </div>
                                <div class="form-group">
                                    <label for="pass_wifi">{{lang('Mật khẩu wifi')}}</label>
                                    <input type="text" name="pass_wifi" parsley-trigger="change" id="pass_wifi"
                                           autocomplete="off"
                                           value="{{!empty($dtData) ? $dtData['pass_wifi'] : ''}}"
                                           class="form-control pass_wifi">
                                </div>
                                <div class="form-group">
                                    <label for="day">Ngày hoạt động</label>
                                    <div style="display: flex;align-items: center;flex-wrap: wrap">
                                        @foreach (getListDay() as $key => $value)
                                            <div class="checkbox checkbox-info"
                                                 style="margin-right: 10px;margin-top: 10px !important;">
                                                <input type="checkbox" name="day[]" {{!empty($arrDate) && in_array($value['id'],
                                                $arrDate) ? 'checked' : ''}}
                                                id="day_{{$value['id']}}"
                                                       value="{{ $value['id']}}">
                                                <label for="day_{{$value['id']}}">{{$value['name']}}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="hour">Thời gian hoạt động</label>
                                    <div class="clearfix"></div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="hour_start">Từ</label>
                                            <input type="time" name="hour_start" parsley-trigger="change"
                                                   id="hour_start" autocomplete="off"
                                                   value="{{!empty($dtData) ? $dtData['hour_start'] : ''}}"
                                                   class="form-control hour_start">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hour_end">Đến</label>
                                            <input type="time" name="hour_end" parsley-trigger="change"
                                                   id="hour_end" autocomplete="off"
                                                   value="{{!empty($dtData) ? $dtData['hour_end'] : ''}}"
                                                   class="form-control hour_end">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div>
                                        <label for="type_lunch_break">Thời gian nghỉ trưa</label>
                                        <br>
                                        <input type="checkbox" name="type_lunch_break"
                                               {{!empty($dtData) ? ($dtData['type_lunch_break'] == 1 ? 'checked' : '') : ''}} class="type_lunch_break"
                                               data-plugin="switchery" data-color="#5fbeaa"/>
                                    </div>
                                </div>
                                <div class="wrap_hour {{!empty($dtData) ? ($dtData['type_lunch_break'] == 1 ? '' : 'hide') : 'hide'}} mb-10">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="hour_start_lunch_break">Từ</label>
                                            <input type="time" name="hour_start_lunch_break" parsley-trigger="change"
                                                   id="hour_start_lunch_break" autocomplete="off"
                                                   value="{{!empty($dtData) ? $dtData['hour_start_lunch_break'] : ''}}"
                                                   class="form-control hour_start_lunch_break">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="hour_end">Đến</label>
                                            <input type="time" name="hour_end_lunch_break" parsley-trigger="change"
                                                   id="hour_end_lunch_break" autocomplete="off"
                                                   value="{{!empty($dtData) ? $dtData['hour_end_lunch_break'] : ''}}"
                                                   class="form-control hour_end_lunch_break">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="other_amenities_car">{{lang('Tiện nghi')}}</label>
                                    <div style="display: flex;flex-wrap: wrap" class="parent_other_amenities_car">
                                        @foreach($otherAmenities as $key => $value)
                                            <div class="other_amenities_car">
                                                <div
                                                    class="chosen_other_amenities_car {{!empty($arrIdOtherAmenitiesCar) ? (in_array($value['id'],$arrIdOtherAmenitiesCar) ? 'active_new' : '') : ''}}">
                                                    <img src="{{ $value['image']}}" style="width: 24px">
                                                    <div>{{$value['name']}}</div>
                                                </div>
                                                <input type="hidden" class="value_other_amenities_car"
                                                       value="{{$value['id']}}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group text-right m-b-0 m-t-10">
                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                            {{lang('dt_save')}}
                        </button>
                        <button type="reset" class="btn btn-default waves-effect waves-light m-l-5 button">
                            <a href="admin/service/list">{{lang('dt_cancel')}}</a>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- end row -->
@endsection
@section('script')
    @include('admin.service.script_js')

    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?=get_option('google_api_key')?>&callback=initMap&libraries=places"
        defer
    ></script>
    <script>
        function initMap() {
            const input = document.getElementById("address");
            const options = {
                fields: ["formatted_address", "geometry", "name", "icon", "photos", "business_status"],
                strictBounds: false,
                componentRestrictions: {country: "vn"}
            };
            const autocomplete = new google.maps.places.Autocomplete(input, options);


            const map = new google.maps.Map(document.getElementById("map"), {
                center: new google.maps.LatLng(51.508742, -0.120850),
                zoom: 20,
                mapTypeControl: false,
            });

            autocomplete.bindTo("bounds", map);

            const infowindow = new google.maps.InfoWindow();
            const infowindowContent = document.getElementById("infowindow-content");
            infowindow.setContent(infowindowContent);
            const marker = new google.maps.Marker({
                map,
                anchorPoint: new google.maps.Point(0, -29),
            });
            if ($('#latitude').val() != "" && $('#longitude').val() != "" && $('#address').val()) {
                var latLng = {
                    lat: parseFloat($('#latitude').val()),
                    lng: parseFloat($('#longitude').val()),
                };
                marker.setPosition(latLng);
                map.setCenter(latLng);
                map.setZoom(20);
                infowindowContent.children["place-name"].textContent = $('#name_location').val();
                ;
                infowindowContent.children["place-address"].textContent = $('#address').val();
                infowindow.open(map, marker);
            }

            autocomplete.addListener("place_changed", () => {
                infowindow.close();
                const place = autocomplete.getPlace();

                if (!place.geometry || !place.geometry.location) {
                    // User entered the name of a Place that was not suggested and
                    // pressed the Enter key, or the Place Details request failed.
                    window.alert("No details available for input: '" + place.name + "'");
                    return;
                }

                // If the place has a geometry, then present it on a map.
                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);
                }
                $('#latitude').val(place.geometry.location.lat());
                $('#longitude').val(place.geometry.location.lng());
                $('#name_location').val(place.name);
                console.log(infowindowContent)
                marker.setPosition(place.geometry.location);
                // marker.setVisible(true);
                infowindowContent.children["place-name"].textContent = place.name;
                infowindowContent.children["place-address"].textContent = place.formatted_address;
                infowindow.open(map, marker);
            });
        }

        window.initMap = initMap;

    </script>
@endsection
