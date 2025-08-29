@extends('admin.layouts.index')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{$title}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/service/list">{{$titleService}}</a></li>
                <li class="active">{{ lang('dt_view_service') }}</li>
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
                    <a href="#image" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs">Hình ảnh</span>
                        <span class="hidden-xs">Hình ảnh</span>
                    </a>
                </li>
                <li>
                    <a href="#transaction" data-toggle="tab" aria-expanded="true">
                        <span class="visible-xs">Lịch sử chuyến đi</span>
                        <span class="hidden-xs">Lịch sử chuyến đi</span>
                    </a>
                </li>
                <li>
                    <a href="#review" data-toggle="tab" aria-expanded="true">
                        <span class="visible-xs">Đánh giá gian hàng</span>
                        <span class="hidden-xs">Đánh giá gian hàng</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="wrap_image_car">
                        @if(!empty($dtData))
                            @if(!empty($dtData['image']))
                                <div class="image_car">
                                    <a href="{{$dtData['image']}}" data-lightbox="customer-profile"
                                       class="display-block mbot5">
                                        <img src="{{$dtData['image']}}" alt="image"
                                             class="img-responsive img-rounded"
                                             style="width: 300px;height: 250px">
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="wrap_title_car">
                        <div class="title_car">{{$dtData['name']}}</div>
                        <div class="wrap_features_car">
                            <div class="features_car">SĐT: {{$dtData['phone_number']}}</div>
                            @if($dtData['transaction']['total'] > 0)
                                <div class="title_trip">
                                    {!! (!empty($dtData['total_star']) ? '<div class="features_car_star"><img src="admin/assets/images/star.svg"> '.($dtData['total_star']).'</div>' : '') !!}
                                    <div><img src="admin/assets/images/tick-circle.svg">{{$dtData['transaction']['total']}} chuyến đi
                                    </div>
                                </div>
                            @else
                                <div style="display: flex;align-items: center">Chưa có chuyến</div>
                            @endif
                        </div>
                        <div class="wrap_features_car">
                            <div><img src="{{$dtData['category_service']['icon']}}" style="width: 20px">{{$dtData['category_service']['name']}}</div>
                        </div>
                    </div>
                    <div class="wrap_detail_car">
                        <div class="title_detail">{{lang('dt_detail')}}</div>
                        <div class="detail_car">{!! htmlspecialchars_decode($dtData['detail']) !!}</div>
                    </div>
                    <div class="wrap_other_amenities_car">
                        <div class="title_other_amenities_car">{{lang('Ngày hoạt động')}}</div>
                        <div class="wrap_other_amenities">
                            @if(!empty(!empty($dtData['day'])))
                                @foreach($dtData['day'] as $key => $value)
                                    <div class="wrap_other_amenities_detail" style="min-width: 10%">
                                        <div class="other_amenities_text_title">{{ getListDay($value['day']) }}</div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="wrap_other_amenities_car">
                        <div class="title_other_amenities_car">{{lang('dt_other_amenities_car')}}</div>
                        <div class="wrap_other_amenities">
                            @if(!empty(!empty($dtData['other_amenities'])))
                                @foreach($dtData['other_amenities'] as $key => $value)
                                    <div class="wrap_other_amenities_detail">
                                        <div class="other_amenities_detail_image"><img
                                                style="width: 35px;height: 35px;margin-right: 5px"
                                                src="{{$value['image']}}"></div>
                                        <div class="other_amenities_text_title">{{ $value['name'] }}</div>
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
                                    $dtImage = !empty($dtData['customer']['avatar']) ? $dtData['customer']['avatar'] : imgDefault();
                                @endphp
                                <div class="detail_customer_image"><img class="img-responsive img-circle"
                                                                        style="width: 52px;margin-right: 10px"
                                                                        src="{{$dtImage}}"></div>
                                <div class="wrap_detail_customer_text">
                                    <div class="detail_customer_text">{{!empty($dtData['customer']) ? $dtData['customer']['fullname'] : '' }}</div>
                                    <div class="detail_customer_info">
                                        <div>{{!empty($dtData['customer']) ? $dtData['customer']['phone'] : '' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wrap_address_car">
                        <div class="title_address">Địa chỉ gian hàng</div>
                        <div class="address_car"><img src="admin/assets/images/location.svg"
                                                      style="width: 20px;margin-right: 5px">{{ $dtData['address'] }}</div>
                        <div class="address_map_car">
                            <div id="map" style="width:100%;height:300px;"></div>
                            <div id="infowindow-content">
                                <span id="place-name" class="title"></span><br/>
                                <span id="place-address"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="image">
                    <div class="row" style="margin-bottom: 30px">
                        <div class="col-md-6">
                            <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Hình ảnh gian</div>
                            <div class="wrap_image_car">
                                @if(!empty($dtData['image_store']))
                                    @foreach($dtData['image_store'] as $key => $value)
                                        {!! loadImageNew($value['image'],'300px','img-rounded',$value['name'],false,'250px','image_parrot_old') !!}
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="font-size: 17px;font-weight: bold;margin-bottom: 10px">Hình ảnh menu</div>
                            <div class="wrap_image_car">
                                @if(!empty($dtData['image_menu']))
                                    @foreach($dtData['image_menu'] as $key => $value)
                                        {!! loadImageNew($value['image'],'300px','img-rounded',$value['name'],false,'250px','image_parrot_old') !!}
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="transaction">
                    <input type="hidden" name="count_transaction" class="count_transaction"
                           value="{{!empty($dtData['transaction']['total_all']) ? $dtData['transaction']['total_all'] : 0}}">
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
                <div class="tab-pane" id="review">
                    <div class="row">
                        <div class="col-md-3 m-b-10">
                        </div>
                        <div class="col-sm-12">
                            <div class="">
                                <table id="table_review_service" class="table table-bordered table_review_service">
                                    <thead>
                                    <tr>
                                        <th class="text-center">{{lang('dt_stt')}}</th>
                                        <th class="text-center">{{lang('Thành viên')}}</th>
                                        <th class="text-center">{{lang('dt_content')}}</th>
                                        <th class="text-center">{{lang('Tag')}}</th>
                                        <th class="text-center">{{lang('dt_star')}}</th>
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
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script
        src="https://maps.googleapis.com/maps/api/js?key=<?=get_option('google_api_key')?>&callback=initMap&libraries=places"
        defer></script>
    <script>
        limitTransaction = "{{$limitTransaction}}"
        function initMap() {
            @if(!empty($dtData['longitude']) && !empty($dtData['latitude']))
            const map = new google.maps.Map(document.getElementById("map"), {
                center: new google.maps.LatLng({{$dtData['latitude']}}, {{$dtData['longitude']}}),
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
                lat: {{$dtData['latitude']}},
                lng: {{$dtData['longitude']}},
            };
            marker.setPosition(latLng);
            map.setCenter(latLng);
            map.setZoom(20);
            infowindowContent.children["place-name"].textContent = "{{$dtData['name_location']}}"
            infowindowContent.children["place-address"].textContent = "{{$dtData['address']}}";
            infowindow.open(map, marker);
            @endif

        }

        window.initMap = initMap;

        var arrId = [];

        var fnserverparams = {
            'type_review_search': '#type_review_search'
        };
        var oTable;

        function table_review_service() {
            oTable = InitDataTable('#table_review_service', 'admin/service/getReviewService', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/service/getReviewService/{{$dtData['id']}}",
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
                        data: 'id', name: 'id', width: "80px"
                    },
                    {data: 'customer', name: 'customer', width: "200px"},
                    {data: 'content', name: 'content'},
                    {data: 'tag', name: 'tag',width: "200px"},
                    {data: 'star', name: 'star', width: "150px"},
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

        $.each(fnserverparamsNew, function (filterIndex, filterItem) {
            $('' + filterItem).on('change', function () {
                oTableReport.draw('page')
            });
        });
        $(document).on('shown.bs.tab', 'a[href="#review"]', function () {
            table_review_service();
        });
        $(document).on('shown.bs.tab', 'a[href="#transaction"]', function () {
            loadTransaction();
        });
        pageTransaction = 1;
        $(document).ready(function () {
            search_daterangepicker('date_search');
            search_daterangepicker('date_search_end');
        })
        $(document).on('change', '#status_search, #date_search, #date_search_end', function (event) {
            pageTransaction = 1;
            loadTransaction();
        });

        function loadTransaction() {
            $.ajax({
                url: 'admin/service/loadTransaction',
                type: 'POST',
                dataType: 'html',
                cache: false,
                data: {
                    service_id: {{$dtData['id']}},
                    status_search: $("#status_search").val(),
                    type_search: $("#type_search").val(),
                    date_search: $("#date_search").val(),
                    date_search_end: $("#date_search_end").val(),
                },
            }).done(function (data) {
                $(".result_transaction").html(data);
                // if ($('body').height() > $('.result_transaction').height()) {
                //     loadMoreTransaction();
                // }
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
                url: 'admin/service/loadMoreTransaction',
                data: {
                    page: pageTransaction,
                    service_id: {{$dtData['id']}},
                    status_search: $("#status_search").val(),
                    type_search: $("#type_search").val(),
                    date_search: $("#date_search").val(),
                    date_search_end: $("#date_search_end").val(),
                },
                dataType: "html",
                success: function (data) {
                    if (data) {
                        $(`.result_transaction`).append(data);
                        // if ($('body').height() > $('.result_transaction').height()) {
                        //     loadMoreTransaction();
                        // }
                    }
                }
            });
        }

        $(window).scroll(function () {
            if ($(window).scrollTop() >= ($(document).height() - $(window).height())) {
                loadMoreTransaction();
            }
        });
    </script>
@endsection
