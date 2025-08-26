@extends('admin.layouts.index')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/car/list">{{$titleCar}}</a></li>
                <li class="active">{{ lang('dt_view_car') }}</li>
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
                    <a href="#paper_car" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Hình ảnh xe</span>
                        <span class="hidden-xs">Hình ảnh xe</span>
                    </a>
                </li>
                <li class="">
                    <a href="#surcharge_car" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Phụ phí</span>
                        <span class="hidden-xs">Phụ phí</span>
                    </a>
                </li>
                <li class="">
                    <a href="#promotion_car" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Khuyến mãi</span>
                        <span class="hidden-xs">Khuyến mãi</span>
                    </a>
                </li>
                <li>
                    <a href="#transaction" data-toggle="tab" aria-expanded="true">
                        <span class="visible-xs">Lịch sử chuyến</span>
                        <span class="hidden-xs">Lịch sử chuyến</span>
                    </a>
                </li>
                <li>
                    <a href="#review_car" data-toggle="tab" aria-expanded="true">
                        <span class="visible-xs">Đánh giá xe</span>
                        <span class="hidden-xs">Đánh giá xe</span>
                    </a>
                </li>
                <li>
                    <a href="#report_car" data-toggle="tab" aria-expanded="true">
                        <span class="visible-xs">Báo cáo xe</span>
                        <span class="hidden-xs">Báo cáo xe</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="wrap_image_car">
                        @if(!empty($car->image_car))
                            @foreach($car->image_car as $key => $value)
                                <div class="image_car">
                                    <a href="{{asset('storage/'.$value->name)}}" data-lightbox="customer-profile"
                                       class="display-block mbot5">
                                        <img src="{{asset('storage/'.$value->name)}}" alt="image"
                                             class="img-responsive img-rounded"
                                             style="width: 300px;height: 250px">
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="wrap_title_car">
                        <div class="title_car">{{$car->name}}</div>
                        <div class="wrap_features_car">
                            <div class="features_car">{{getValueTransmission($car->transmission)}}</div>
                            {!! !empty($car->book_car_flash) ? '<div class="features_car">'.lang('dt_book_car_flash').'</div>' : '' !!}
                            {!! !empty($car->delivery_car) ? '<div class="features_car">'.lang('dt_delivery_car').'</div>' : '' !!}
                            {!! ($car->mortgage == 0) ? '<div class="features_car">'.lang('dt_not_mortgage').'</div>' : '' !!}
                            @if(!empty($car->transaction->count()))
                                @if($car->type == 1)
                                    <div class="title_trip">
                                        <div style="margin-right: 5px">Xe tự lái</div>
                                        {!! (!empty($car->review_car->avg('star')) ? '<div class="features_car_star"><img src="admin/assets/images/star.svg"> '.formatNumber($car->review_car->avg('star')).'</div>' : '') !!}
                                        <div><img
                                                src="admin/assets/images/tick-circle.svg">{{$car->transaction_finish->count()}}
                                            chuyến
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($car->car_talent))
                                    <div class="title_trip">
                                        <div style="margin-right: 5px">Xe có tài</div>
                                        {!! (!empty($car->review_car_talent->avg('star')) ? '<div class="features_car_star"><img src="admin/assets/images/star.svg"> '.formatNumber($car->review_car_talent->avg('star')).'</div>' : '') !!}
                                        <div><img
                                                src="admin/assets/images/tick-circle.svg">{{$car->transaction_finish_talent->count()}}
                                            chuyến
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div style="display: flex;align-items: center">Chưa có chuyến</div>
                            @endif
                        </div>
                    </div>
                    <div class="wrap_characteristic_car">
                        <div class="title_characteristic">Đặc điểm</div>
                        <div class="wrap_characteristic">
                            <div class="wrap_characteristic_detail">
                                <div class="characteristic_detail_image"><img
                                        src="admin/assets/images/characteristic_1.svg"></div>
                                <div class="characteristic_detail_text">
                                    <div class="characteristic_detail_text_title">{{lang('dt_transmission')}}</div>
                                    <div
                                        class="characteristic_detail_text_value">{{getValueTransmission($car->transmission)}}</div>
                                </div>
                            </div>
                            <div class="wrap_characteristic_detail">
                                <div class="characteristic_detail_image"><img
                                        src="admin/assets/images/characteristic_2.svg"></div>
                                <div class="characteristic_detail_text">
                                    <div class="characteristic_detail_text_title">{{lang('dt_number_seat_car')}}</div>
                                    <div class="characteristic_detail_text_value">{{($car->number_seat)}} chỗ</div>
                                </div>
                            </div>
                            <div class="wrap_characteristic_detail">
                                <div class="characteristic_detail_image"><img
                                        src="admin/assets/images/characteristic_3.svg"></div>
                                <div class="characteristic_detail_text">
                                    <div class="characteristic_detail_text_title">Nhiên liệu</div>
                                    <div
                                        class="characteristic_detail_text_value">{{getValueTypeFuel($car->type_fuel)}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wrap_detail_car">
                        <div class="title_detail">{{lang('dt_detail')}}</div>
                        <div class="detail_car">{!! htmlspecialchars_decode($car->detail) !!}</div>
                    </div>
                    <div class="wrap_other_amenities_car">
                        <div class="title_other_amenities_car">{{lang('dt_other_amenities_car')}}</div>
                        <div class="wrap_other_amenities">
                            @if(!empty($car->other_amenities_car))
                                @foreach($car->other_amenities_car  as $key => $value)
                                    <div class="wrap_other_amenities_detail">
                                        <div class="other_amenities_detail_image"><img
                                                style="width: 35px;height: 35px;margin-right: 5px"
                                                src="{{asset('storage/'.$value['image'])}}"></div>
                                        <div class="other_amenities_text_title">{{ $value->name }}</div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="wrap_customer_car">
                        <div class="title_customer_car">{{lang('dt_customer')}}</div>
                        <div class="wrap_detail_customer_car">
                            <div class="wrap_detail_customer" style="margin-right: 50px">
                                @php
                                    $dtImage = !empty($car->customer->avatar) ? url('/'.$car->customer->avatar) : imgDefault();
                                @endphp
                                <div class="detail_customer_image"><img class="img-responsive img-circle"
                                                                        style="width: 52px;margin-right: 10px"
                                                                        src="{{$dtImage}}"></div>
                                <div class="wrap_detail_customer_text">
                                    <div class="detail_customer_text">{{ $car->customer->fullname }}</div>
                                    @if(!empty($car->transaction->count()))
                                        <div class="detail_customer_info">
                                            @if($car->type == 1)
                                                <div class="title_trip">
                                                    <div style="margin-right: 5px">Xe tự lái</div>
                                                    {!! (!empty($car->review_car->avg('star')) ? '<div style="margin-right: 15px"><img src="admin/assets/images/star.svg"> '.formatNumber($car->review_car->avg('star')).'</div>' : '') !!}
                                                    <div><img
                                                            src="admin/assets/images/tick-circle.svg">{{$car->transaction_finish->count()}}
                                                        chuyến
                                                    </div>
                                                </div>
                                            @endif
                                            @if(!empty($car->car_talent))
                                                <div class="title_trip">
                                                    <div style="margin-right: 5px">Xe có tài</div>
                                                    {!! (!empty($car->review_car_talent->avg('star')) ? '<div style="margin-right: 15px"><img src="admin/assets/images/star.svg"> '.formatNumber($car->review_car_talent->avg('star')).'</div>' : '') !!}
                                                    <div><img
                                                            src="admin/assets/images/tick-circle.svg">{{$car->transaction_finish_talent->count()}}
                                                        chuyến
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="detail_customer_info">
                                            <div>Chưa có chuyến</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="wrap_detail_customer">
                                <div>Chủ xe 5 sao có thời gian phản hồi nhanh chóng, tỉ lệ đồng ý cao, mức giá cạnh
                                    tranh và dịch vụ nhận được nhiều đánh giá tốt từ khách hàng
                                </div>
                            </div>
                        </div>
                        <div class="wrap_customer_feedback">
                            <div class="customer_feedback">
                                <div class="customer_feedback_title">Tỉ lệ phản hồi</div>
                                <div class="customer_feedback_value">100%</div>
                            </div>
                            <div class="customer_feedback">
                                <div class="customer_feedback_title">Tỉ lệ đồng ý</div>
                                <div class="customer_feedback_value">100%</div>
                            </div>
                            <div class="customer_feedback">
                                <div class="customer_feedback_title">Phản hồi trong</div>
                                <div class="customer_feedback_value">100%</div>
                            </div>
                        </div>
                    </div>
                    <div class="wrap_address_car">
                        <div class="title_address">Vị trí xe</div>
                        <div class="address_car"><img src="admin/assets/images/location.svg"
                                                      style="width: 20px;margin-right: 5px">{{ $car->address }}</div>
                        <div class="address_map_car">
                            <div id="map" style="width:100%;height:300px;"></div>
                            <div id="infowindow-content">
                                <span id="place-name" class="title"></span><br/>
                                <span id="place-address"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="paper_car">
                    <form id="imageCarForm" action="admin/car/updateImageCar/{{$car->id}}" method="post" data-parsley-validate
                          novalidate
                          enctype="multipart/form-data">
                        {{csrf_field()}}
                        <div class="row" style="margin-bottom: 30px">
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Cà vẹt xe</div>
                                <div class="form-group">
                                    <input type="file" name="image_parrot[]" id="image_parrot" data-input="false" multiple
                                               class="filestyle image_parrot"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_parrot_car))
                                        @foreach($car->image_parrot_car as $key => $value)
                                        {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_parrot_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Đăng kiểm xe</div>
                                <div class="form-group">
                                    <input type="file" name="image_registry[]" id="image_registry" data-input="false" multiple
                                               class="filestyle image_registry"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_registry_car))
                                        @foreach($car->image_registry_car as $key => $value)
                                        {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_registry_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Bảo hiểm xe</div>
                                <div class="form-group">
                                    <input type="file" name="image_insurance[]" id="image_insurance" data-input="false" multiple
                                               class="filestyle image_insurance"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_insurance_car))
                                        @foreach($car->image_insurance_car as $key => $value)
                                            {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_insurance_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-bottom: 30px">
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Ảnh mặt trước</div>
                                <div class="form-group">
                                    <input type="file" name="image_car_position_before[]" id="image_car_position_before" data-input="false"
                                               class="filestyle image_car_position_before"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_car_position_before))
                                        @foreach($car->image_car_position_before as $key => $value)
                                        {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_car_position_before_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Ảnh mặt sau</div>
                                <div class="form-group">
                                    <input type="file" name="image_car_position_affter[]" id="image_car_position_affter" data-input="false"
                                               class="filestyle image_car_position_affter"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_car_position_affter))
                                        @foreach($car->image_car_position_affter as $key => $value)
                                        {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_car_position_affter_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                         <div class="row" style="margin-bottom: 30px">
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Ảnh bên trái</div>
                                <div class="form-group">
                                    <input type="file" name="image_car_position_left[]" id="image_car_position_left" data-input="false"
                                               class="filestyle image_car_position_left"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_car_position_left))
                                        @foreach($car->image_car_position_left as $key => $value)
                                        {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_car_position_left_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Ảnh bên phải</div>
                                <div class="form-group">
                                    <input type="file" name="image_car_position_right[]" id="image_car_position_right" data-input="false"
                                               class="filestyle image_car_position_right"
                                               data-buttonbefore="true">
                                </div>
                                <div class="wrap_image_car">
                                    @if(!empty($car->image_car_position_right))
                                        @foreach($car->image_car_position_right as $key => $value)
                                        {!! loadImageNew(asset('storage/'.$value->name),'300px','img-rounded',$value->name,true,'250px','image_car_position_right_old') !!}
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group text-right m-b-0 m-t-10">
                            <button class="btn btn-primary waves-effect waves-light" type="submit">
                                {{lang('dt_save')}}
                            </button>
                        </div>
                    </form>
                </div>
                <div class="tab-pane" id="surcharge_car">
                    <div class="row">
                        <div class="col-md-8 result_surcharge_car">
                            <div class="{{$car->type == 1 ? '' : 'hide'}}">
                                <div class="title-header">{{lang('dt_car_1')}}</div>
                                @if(!empty($dtSurchargeCar))
                                    @foreach($dtSurchargeCar as $key => $value)
                                        <div style="display: flex;justify-content: space-between;align-items: center"
                                             class="wrap_checkbox">
                                            <input type="hidden" name="type" value="1">
                                            <div class="checkbox checkbox-custom" style="width: 50%">
                                                <input
                                                    @foreach($car->surcharge_car as $surcharge_car)
                                                        @if($surcharge_car->id == $value->id)
                                                            {{'checked'}}
                                                        @endif
                                                    @endforeach
                                                    id="surcharge_car_{{$value->id}}" data-id="{{$value->id}}"
                                                    value="{{$value->id}}" type="checkbox" class="surcharge_car"
                                                    name="surcharge_car">
                                                <label for="surcharge_car_{{$value->id}}" class="checkbox-surcharge">
                                                    {{$value->name}}
                                                    <div>{{$value->value}}</div>
                                                </label>
                                                <div style="width: 80%;margin-left: 5px">{{$value->note}}</div>
                                            </div>
                                            <div class="range" style="width: 50%">
                                                <input type="text" id="value_{{$value->id}}"
                                                       class="value_{{$value->id}} valueSurcharge">
                                            </div>
                                            <div class="btn btn-default btn-sm" style="height: 30px;margin-left: 15px"
                                                 onclick="update_surcharge(this,1)">Cập nhật
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="{{!empty($car->car_talent) ? '' : 'hide'}}">
                                <div class="title-header">{{lang('dt_car_2')}}</div>
                                @if(!empty($dtSurchargeCarTalent))
                                    @foreach($dtSurchargeCarTalent as $key => $value)
                                        <div style="display: flex;justify-content: space-between;align-items: center"
                                             class="wrap_checkbox">
                                            <input type="hidden" name="type" value="2">
                                            <div class="checkbox checkbox-custom" style="width: 50%">
                                                <input
                                                    @foreach($car->surcharge_car_talent as $surcharge_car)
                                                        @if($surcharge_car->id == $value->id)
                                                            {{'checked'}}
                                                        @endif
                                                    @endforeach
                                                    id="surcharge_car_{{$value->id}}" data-id="{{$value->id}}"
                                                    value="{{$value->id}}" type="checkbox" class="surcharge_car"
                                                    name="surcharge_car">
                                                <label for="surcharge_car_{{$value->id}}" class="checkbox-surcharge">
                                                    {{$value->name}}
                                                    <div>{{$value->value}}</div>
                                                </label>
                                                <div style="width: 80%;margin-left: 5px">{{$value->note}}</div>
                                            </div>
                                            <div class="range" style="width: 50%">
                                                <input type="text" id="value_{{$value->id}}"
                                                       class="value_{{$value->id}} valueSurcharge">
                                            </div>
                                            <div class="btn btn-default btn-sm" style="height: 30px;margin-left: 15px"
                                                 onclick="update_surcharge(this,2)">Cập nhật
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="promotion_car">
                    @if($car->type == 1)
                        <div class="row m-t-5">
                            <input type="hidden" class="type_car" value="1">
                            <div class="title-header">{{lang('dt_car_1')}}</div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div>
                                        <label for="promotion_first">{{lang('dt_promotion_first')}}</label>
                                        <br>
                                        <input onchange="changeUpdatePromotion(this,1)"
                                               {{!empty($promotion_first) && $promotion_first->active == 1 ? 'checked' : ''}} type="checkbox"
                                               name="promotion_first" class="promotion_first"
                                               data-type="{{Config::get('constant')['promotion_first']}}"
                                               data-plugin="switchery" data-color="#5fbeaa"/>
                                    </div>
                                </div>
                                <div class="row result_promotion_first">
                                    <div class="col-md-12">
                                        <div
                                            class="show_promotion_first {{!empty($promotion_first) && $promotion_first->active == 1 ? '' : 'hide'}} ">
                                            <div class="form-group">
                                                <label for="name_promotion_first">{{lang('dt_promotion_name')}}</label>
                                                <input type="text" name="name_promotion_first"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_first) ? $promotion_first->name : ''}}"
                                                       class="form-control name_promotion_first">
                                            </div>
                                            <div class="form-group">
                                                <label
                                                    for="percent_promotion_first">Số tiền khuyến mãi</label>
                                                <input type="text" name="percent_promotion_first"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_first) ? formatMoney($promotion_first->cash) : 0}}"
                                                       onchange="formatNumBerKeyChange(this)"
                                                       min="0" max="100"
                                                       class="form-control percent_promotion_first">
                                            </div>
                                            <button
                                                class="btn btn-default waves-effect waves-light save_promotion_first"
                                                type="button">
                                                {{lang('dt_save')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div>
                                        <label for="promotion_all">{{lang('dt_promotion_all')}}</label>
                                        <br>
                                        <input onchange="changeUpdatePromotion(this,1)" type="checkbox"
                                               name="promotion_all"
                                               class="promotion_all"
                                               {{!empty($promotion_all) && $promotion_all->active == 1 ? 'checked' : ''}} data-type="{{Config::get('constant')['promotion_all']}}"
                                               data-plugin="switchery" data-color="#5fbeaa"/>
                                    </div>
                                </div>
                                <div class="row result_promotion_all">
                                    <div class="col-md-12">
                                        <div
                                            class="show_promotion_all {{!empty($promotion_all) && $promotion_all->active == 1 ? '' : 'hide'}}">
                                            <div class="form-group">
                                                <label for="name_promotion_all">{{lang('dt_promotion_name')}}</label>
                                                <input type="text" name="name_promotion_all"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_all) ? $promotion_all->name : ''}}"
                                                       class="form-control name_promotion_all">
                                            </div>
                                            <div class="form-group">
                                                <label for="percent_promotion_all">Số tiền khuyến mãi</label>
                                                <input type="text" name="percent_promotion_all"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_all) ? formatMoney($promotion_all->cash) : 0}}"
                                                       onchange="formatNumBerKeyChange(this)"
                                                       class="form-control percent_promotion_all">
                                            </div>
                                            <button class="btn btn-default waves-effect waves-light save_promotion_all"
                                                    type="button">
                                                {{lang('dt_save')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(!empty($car->car_talent))
                        <div class="row m-t-5">
                            <input type="hidden" class="type_car_talent" value="2">
                            <div class="title-header">{{lang('dt_car_2')}}</div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div>
                                        <label for="promotion_first">{{lang('dt_promotion_first')}}</label>
                                        <br>
                                        <input onchange="changeUpdatePromotion(this,2)"
                                               {{!empty($promotion_first_talent) && $promotion_first_talent->active == 1 ? 'checked' : ''}} type="checkbox"
                                               name="promotion_first" class="promotion_first_talent"
                                               data-type="{{Config::get('constant')['promotion_first']}}"
                                               data-plugin="switchery" data-color="#5fbeaa"/>
                                    </div>
                                </div>
                                <div class="row result_promotion_first_talent">
                                    <div class="col-md-12">
                                        <div
                                            class="show_promotion_first_talent {{!empty($promotion_first_talent) && $promotion_first_talent->active == 1 ? '' : 'hide'}} ">
                                            <div class="form-group">
                                                <label for="name_promotion_first">{{lang('dt_promotion_name')}}</label>
                                                <input type="text" name="name_promotion_first_talent"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_first_talent) ? $promotion_first_talent->name : ''}}"
                                                       class="form-control name_promotion_first_talent">
                                            </div>
                                            <div class="form-group">
                                                <label
                                                    for="percent_promotion_first">Số tiền khuyến mãi</label>
                                                <input type="text" name="percent_promotion_first_talent"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_first_talent) ? formatMoney($promotion_first_talent->cash) : 0}}"
                                                       onchange="formatNumBerKeyChange(this)"
                                                       min="0" max="100"
                                                       class="form-control percent_promotion_first_talent">
                                            </div>
                                            <button
                                                class="btn btn-default waves-effect waves-light save_promotion_first_talent"
                                                type="button">
                                                {{lang('dt_save')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div>
                                        <label for="promotion_all">{{lang('dt_promotion_all')}}</label>
                                        <br>
                                        <input onchange="changeUpdatePromotion(this,2)" type="checkbox"
                                               name="promotion_all_talent"
                                               class="promotion_all_talent"
                                               {{!empty($promotion_all_talent) && $promotion_all_talent->active == 1 ? 'checked' : ''}} data-type="{{Config::get('constant')['promotion_all']}}"
                                               data-plugin="switchery" data-color="#5fbeaa"/>
                                    </div>
                                </div>
                                <div class="row result_promotion_all_talent">
                                    <div class="col-md-12">
                                        <div
                                            class="show_promotion_all_talent {{!empty($promotion_all_talent) && $promotion_all_talent->active == 1 ? '' : 'hide'}}">
                                            <div class="form-group">
                                                <label for="name_promotion_all">{{lang('dt_promotion_name')}}</label>
                                                <input type="text" name="name_promotion_all_talent"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_all_talent) ? $promotion_all_talent->name : ''}}"
                                                       class="form-control name_promotion_all_talent">
                                            </div>
                                            <div class="form-group">
                                                <label for="percent_promotion_all">Số tiền khuyến mãi</label>
                                                <input type="text" name="percent_promotion_all_talent"
                                                       autocomplete="off"
                                                       value="{{!empty($promotion_all_talent) ? formatMoney($promotion_all_talent->cash) : 0}}"
                                                       onchange="formatNumBerKeyChange(this)"
                                                       class="form-control percent_promotion_all_talent">
                                            </div>
                                            <button
                                                class="btn btn-default waves-effect waves-light save_promotion_all_talent"
                                                type="button">
                                                {{lang('dt_save')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="tab-pane" id="transaction">
                    <input type="hidden" name="count_transaction" class="count_transaction"
                           value="{{$car->transaction->count()}}">
                    <input type="hidden" name="next" class="next" value="">
                    <div class="row m-b-10">
                        <div class="col-md-3">
                            <label for="status_search">{{lang('dt_status')}}</label>
                            <select class="status_search select2" id="status_search"
                                    data-placeholder="Chọn ..." name="status_search">
                                <option value="-1" selected>Tất cả</option>
                                @foreach($dtStatusTransaction as $value)
                                    <option value="{{$value['id']}}">{{$value['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="type_search">{{lang('dt_type')}}</label>
                            <select class="type_search select2" id="type_search"
                                    data-placeholder="Chọn ..." name="type_search">
                                <option value="-1" selected>Tất cả</option>
                                @foreach($dtTypeCar as $value)
                                    <option value="{{$value['id']}}">{{$value['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_search">Thời gian bắt đầu</label>
                            <input class="form-control date_search" type="text" id="date_search" name="date_search"
                                   value="" autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="date_search_end">Thời gian kết thúc</label>
                            <input class="form-control date_search_end" type="text" id="date_search_end"
                                   name="date_search_end" value="" autocomplete="off">
                        </div>
                    </div>
                    <div class="row m-b-10">
                        <div class="col-md-12 result_transaction">

                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="review_car">
                    <div class="row">
                        <div class="col-md-3 m-b-10">
                            <label for="type_review_search">{{lang('dt_type')}}</label>
                            <select class="type_review_search select2" id="type_review_search"
                                    data-placeholder="Chọn ..." name="type_review_search">
                                <option value="-1" selected>Tất cả</option>
                                @foreach($dtTypeCar as $value)
                                    <option value="{{$value['id']}}">{{$value['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-12">
                            <div class="">
                                <table id="table_review_car" class="table table-bordered table_review_car">
                                    <thead>
                                    <tr>
                                        <th class="text-center">{{lang('dt_stt')}}</th>
                                        <th class="text-center">{{lang('dt_transaction')}}</th>
                                        <th class="text-center">{{lang('dt_renter_car')}}</th>
                                        <th class="text-center">{{lang('dt_content')}}</th>
                                        <th class="text-center">{{lang('dt_star')}}</th>
                                        <th class="text-center">{{lang('dt_type')}}</th>
                                        <th class="text-center">{{lang('dt_time')}}</th>
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
                <div class="tab-pane" id="report_car">
                    <div class="row">
                        <div class="col-md-3 m-b-10">
                            <label for="type_report_search">{{lang('dt_type')}}</label>
                            <select class="type_report_search select2" id="type_report_search"
                                    data-placeholder="Chọn ..." name="type_report_search">
                                <option value="-1" selected>Tất cả</option>
                                @foreach($dtTypeCar as $value)
                                    <option value="{{$value['id']}}">{{$value['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-12">
                            <div class="">
                                <table id="table_report_car" class="table table-bordered table_report_car">
                                    <thead>
                                    <tr>
                                        <th class="text-center">{{lang('dt_stt')}}</th>
                                        <th class="text-center">{{lang('dt_renter_car')}}</th>
                                        <th class="text-center">{{lang('dt_report_car')}}</th>
                                        <th class="text-center">{{lang('dt_note')}}</th>
                                        <th class="text-center">{{lang('dt_type')}}</th>
                                        <th class="text-center">{{lang('dt_time')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?=get_option('google_api_key')?>&callback=initMap&libraries=places"
        defer></script>
    <script>
        @if(!empty($dtSurchargeCar))
        @foreach($dtSurchargeCar as $key => $value)
        var value = 0
        @foreach($car->surcharge_car as $surcharge_car)
            @if($surcharge_car->id == $value->id)
            value = {{$surcharge_car->pivot->value}}
        @endif
        @endforeach
        initRangeSlider('#value_{{$value->id}}', {{$value->min}}, {{$value->max}}, value, {{$value->range}});
        @endforeach
        @endif

        @if(!empty($dtSurchargeCarTalent))
        @foreach($dtSurchargeCarTalent as $key => $value)
        var value = 0
        @foreach($car->surcharge_car_talent as $surcharge_car)
            @if($surcharge_car->id == $value->id)
            value = {{$surcharge_car->pivot->value}}
        @endif
        @endforeach
        initRangeSlider('#value_{{$value->id}}', {{$value->min}}, {{$value->max}}, value);
        @endforeach
            @endif

            limitTransaction = "{{$limitTransaction}}"
        $(document).on('change', '.promotion_first', function (e) {
            checked = $(this).is(':checked');
            if (checked) {
                $(".show_promotion_first").removeClass('hide');
            } else {
                $(".show_promotion_first").addClass('hide');
            }
        })
        $(document).on('change', '.promotion_all', function (e) {
            checked = $(this).is(':checked');
            if (checked) {
                $(".show_promotion_all").removeClass('hide');
            } else {
                $(".show_promotion_all").addClass('hide');
            }
        })

        $(document).on('change', '.promotion_first_talent', function (e) {
            checked = $(this).is(':checked');
            if (checked) {
                $(".show_promotion_first_talent").removeClass('hide');
            } else {
                $(".show_promotion_first_talent").addClass('hide');
            }
        })
        $(document).on('change', '.promotion_all_talent', function (e) {
            checked = $(this).is(':checked');
            if (checked) {
                $(".show_promotion_all_talent").removeClass('hide');
            } else {
                $(".show_promotion_all_talent").addClass('hide');
            }
        })

        function changeUpdatePromotion(_this, type_car) {
            checked = $(_this).is(':checked');
            type = $(_this).attr('data-type');
            if (checked == false) {
                $.ajax({
                    url: 'admin/car/updatePromotionCar',
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    data: {
                        car_id: {{$car->id}},
                        active: 0,
                        type: type,
                        type_car: type_car,
                    },
                }).done(function (data) {
                    if (data.result) {
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    if (type_car == 1) {
                        if (type == 1) {
                            $(".result_promotion_first").html(data.html);
                        } else {
                            $(".result_promotion_all").html(data.html);
                        }
                    } else {
                        if (type == 1) {
                            $(".result_promotion_first_talent").html(data.html);
                        } else {
                            $(".result_promotion_all_talent").html(data.html);
                        }
                    }

                }).fail(function () {
                })
            }
        }

        $(document).on('click', '.save_promotion_first', function (e) {
            $.ajax({
                url: 'admin/car/updatePromotionCar',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    car_id: {{$car->id}},
                    active: 1,
                    type: 1,
                    type_car: 1,
                    name_promotion_first: $(".name_promotion_first").val(),
                    percent_promotion_first: $(".percent_promotion_first").val(),
                },
            }).done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                $(".result_promotion_first").html(data.html);
            }).fail(function () {
            })
        });

        $(document).on('click', '.save_promotion_all', function (e) {
            $.ajax({
                url: 'admin/car/updatePromotionCar',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    car_id: {{$car->id}},
                    active: 1,
                    type: 2,
                    type_car: 1,
                    name_promotion_first: $(".name_promotion_all").val(),
                    percent_promotion_first: $(".percent_promotion_all").val(),
                },
            }).done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                $(".result_promotion_all").html(data.html);
            }).fail(function () {
            })
        });

        $(document).on('click', '.save_promotion_first_talent', function (e) {
            $.ajax({
                url: 'admin/car/updatePromotionCar',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    car_id: {{$car->id}},
                    active: 1,
                    type: 1,
                    type_car: 2,
                    name_promotion_first: $(".name_promotion_first_talent").val(),
                    percent_promotion_first: $(".percent_promotion_first_talent").val(),
                },
            }).done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                $(".result_promotion_first_talent").html(data.html);
            }).fail(function () {
            })
        });

        $(document).on('click', '.save_promotion_all_talent', function (e) {
            $.ajax({
                url: 'admin/car/updatePromotionCar',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    car_id: {{$car->id}},
                    active: 1,
                    type: 2,
                    type_car: 2,
                    name_promotion_first: $(".name_promotion_all_talent").val(),
                    percent_promotion_first: $(".percent_promotion_all_talent").val(),
                },
            }).done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                $(".result_promotion_all_talent").html(data.html);
            }).fail(function () {
            })
        });

        function initMap() {
            @if(!empty($car->longitude) && !empty($car->latitude))
            const map = new google.maps.Map(document.getElementById("map"), {
                center: new google.maps.LatLng({{$car->latitude}}, {{$car->longitude}}),
                zoom: 20,
                mapTypeControl: false,
            });
            const infowindow = new google.maps.InfoWindow();
            const infowindowContent = document.getElementById("infowindow-content");
            infowindow.setContent(infowindowContent);
            const marker = new google.maps.Marker({
                map,
                anchorPoint: new google.maps.Point(0, -29),
            });

            var latLng = {
                lat: {{$car->latitude}},
                lng: {{$car->longitude}},
            };
            marker.setPosition(latLng);
            map.setCenter(latLng);
            map.setZoom(20);
            infowindowContent.children["place-name"].textContent = "{{$car->name_location}}"
            infowindowContent.children["place-address"].textContent = "{{$car->address}}";
            infowindow.open(map, marker);
            @endif

        }

        window.initMap = initMap;

        var arrId = [];

        function update_surcharge(_this, type) {
            updateSurcharge($(_this).closest('div.wrap_checkbox').find('input.surcharge_car')[0], type);
        }

        function updateSurcharge(_this, type) {
            $.ajax({
                url: 'admin/car/updateSurcharge',
                type: 'get',
                dataType: 'JSON',
                cache: false,
                data: {
                    car_id: {{$car->id}},
                    checked: $(_this).prop('checked'),
                    surcharge_car: $(_this).attr('data-id'),
                    type: type,
                    value: $(`#value_${$(_this).attr('data-id')}`).val()
                },
            }).done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                $(".result_surcharge_car").html(data.html);
                if (data.dtSurchargeCar.length > 0) {
                    $.each(data.dtSurchargeCar, function (k, v) {
                        initRangeSlider(`#value_${v.id}`, v.min, v.max, v.value, v.range);
                    })
                }
            }).fail(function () {
            })
        }

        var fnserverparams = {
            'type_review_search': '#type_review_search'
        };
        var oTable;

        function table_review_car() {
            oTable = InitDataTable('#table_review_car', 'admin/car/getReviewCar', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/car/getReviewCar/{{$car->id}}",
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
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</data>`;
                        },
                        data: 'DT_RowIndex', name: 'DT_RowIndex', width: "80px"
                    },
                    {data: 'transaction', name: 'transaction', width: "200px"},
                    {data: 'customer', name: 'customer', width: "200px"},
                    {data: 'content', name: 'content'},
                    {data: 'star', name: 'star', width: "150px"},
                    {data: 'type', name: 'type', width: "100px"},
                    {data: 'created_at', name: 'created_at', width: "150px"},
                    {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },
                ]
            });
        }

        $.each(fnserverparams, function (filterIndex, filterItem) {
            $('' + filterItem).on('change', function () {
                oTable.draw('page')
            });
        });
        var fnserverparamsNew = {
            'type_report_search': '#type_report_search'
        };
        var oTableReport;

        function table_report_car() {
            oTableReport = InitDataTable('#table_report_car', 'admin/car/getReportCar', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/car/getReportCar/{{$car->id}}",
                    "data": function (d) {
                        for (var key in fnserverparamsNew) {
                            d[key] = $(fnserverparamsNew[key]).val();
                        }
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</data>`;
                        },
                        data: 'DT_RowIndex', name: 'DT_RowIndex', width: "80px"
                    },
                    {data: 'customer', name: 'customer', width: "200px"},
                    {data: 'report', name: 'report', width: "200px"},
                    {data: 'note', name: 'note'},
                    {data: 'type', name: 'type', width: "100px"},
                    {data: 'created_at', name: 'created_at', width: "150px"},
                ]
            });
        }

        $.each(fnserverparamsNew, function (filterIndex, filterItem) {
            $('' + filterItem).on('change', function () {
                oTableReport.draw('page')
            });
        });
        $(document).on('shown.bs.tab', 'a[href="#report_car"]', function () {
            table_report_car();
        });
        $(document).on('shown.bs.tab', 'a[href="#review_car"]', function () {
            table_review_car();
        });
        $(document).on('shown.bs.tab', 'a[href="#transaction"]', function () {
            loadTransaction();
        });
        pageTransaction = 1;
        $(document).ready(function () {
            search_daterangepicker('date_search');
            search_daterangepicker('date_search_end');
        })
        $(document).on('change', '#status_search, #type_search, #date_search, #date_search_end', function (event) {
            pageTransaction = 1;
            loadTransaction();
        });

        function loadTransaction() {
            $.ajax({
                url: 'admin/car/loadTransaction',
                type: 'POST',
                dataType: 'html',
                cache: false,
                data: {
                    car_id: {{$car->id}},
                    status_search: $("#status_search").val(),
                    type_search: $("#type_search").val(),
                    date_search: $("#date_search").val(),
                    date_search_end: $("#date_search_end").val(),
                },
            }).done(function (data) {
                $(".result_transaction").html(data);
                if ($('body').height() > $('.result_transaction').height()) {
                    loadMoreTransaction();
                }
            }).fail(function () {
            })
        }

        function loadMoreTransaction() {
            next = $(".next").val();
            if (next == 0) {
                return;
            }
            pageTransaction++
            countTransaction = $(".count_transaction").val();
            count_page = Math.ceil(countTransaction / limitTransaction);
            $.ajax({
                type: "POST",
                url: 'admin/car/loadMoreTransaction',
                data: {
                    page: pageTransaction,
                    car_id: {{$car->id}},
                    status_search: $("#status_search").val(),
                    type_search: $("#type_search").val(),
                    date_search: $("#date_search").val(),
                    date_search_end: $("#date_search_end").val(),
                },
                dataType: "html",
                success: function (data) {
                    if (data) {
                        $(`.result_transaction`).append(data);
                        if ($('body').height() > $('.data_notification').height()) {
                            loadMoreTransaction();
                        }
                    }
                }
            });
        }

        $(window).scroll(function () {
            if ($(window).scrollTop() >= ($(document).height() - $(window).height())) {
                loadMoreTransaction();
            }
        });

        $("#imageCarForm").validate({
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
                        alert_float('success',data.message);
                        window.location.href='admin/car/view/'+data.car_id;
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
