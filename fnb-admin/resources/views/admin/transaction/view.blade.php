<style>
</style>
<div class="modal-dialog transaction-modal" style="width: 70%;">
    <div class="modal-content" style="background: #eee">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}} - {{$transaction->reference_no}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="transaction-left">
                        <div class="wrap-info-car">
                            <div class="info-car-left">
                                @php
                                    $image_car = !empty($transaction->car->image_car) ? $transaction->car->image_car : null;
                                    $dtImage = null;
                                    if (!empty($image_car)){
                                        $dtImage = !empty($image_car[0]->name) ? asset('storage/'.$image_car[0]->name) : null;
                                    }
                                @endphp
                                {!! loadImage($dtImage,0,'img-rounded','',false); !!}
                            </div>
                            <div class="info-car-right">
                                <div><span class="title-car"><a style="color: unset" href="admin/car/view/{{$transaction->car_id}}" target="_blank">{{$transaction->car->name}}</a></span><i
                                        style="font-size: 10px;padding-left: 10px" class="ion-record"></i><span
                                        style="font-size: 18px;padding-left: 10px">{{getValueTypeCar($transaction->type)}}</span>
                                </div>
                                <div
                                    class="title-transmission features_car">{{getValueTransmission($transaction->car->transmission)}}</div>
                                <div class="title-address"><img src="admin/assets/images/location.svg"
                                                                style="width: 20px;margin-right: 5px">{{$transaction->car->district->name.','.$transaction->car->province->name}}
                                </div>
                            </div>
                        </div>
                        <div class="wrap-transaction-time">
                            <div class="title-transaction-time">Hợp đồng thuê xe</div>
                            <div style="margin-bottom: 10px;margin-top: 5px;display: flex;justify-content: space-between;font-size: 15px">
                                <div>{!! !empty($transaction->contract) ? '<a target="_blank" href="admin/pdf/contractPdf/'.$transaction->contract->id.'"><img src="'.asset('admin/assets/images/attachments.svg').'"> Xem hợp đồng</a>' : '' !!} </div>
                                <div>{!! !empty($transaction->handover_record) ? '<a target="_blank" href="admin/pdf/handoverRecordPdf/'.$transaction->handover_record->id.'"><img src="'.asset('admin/assets/images/attachments.svg').'"> Xem biên bản bàn giao</a>' : '' !!} </div>
                            </div>
                            <div class="title-transaction-time">Thời gian thuê xe</div>
                            <div class="transaction-time">
                                <div class="left-transaction-time">
                                    <div class="title_start-left"><i class="fa fa-calendar"></i> {{lang('dt_start')}}
                                    </div>
                                    <div class="value_start-left">{{_dt_new($transaction->date_start)}}</div>
                                </div>
                                <div class="right-transaction-time">
                                    <div class="title_start-right"><i class="fa fa-calendar"></i> {{lang('dt_end')}}
                                    </div>
                                    <input type="text" class="datetimepickerNew form-control value_start-right" value="{{_dt_new($transaction->date_end)}}">
                                    <input type="hidden" class="form-control date_end" value="{{_dt($transaction->date_end)}}">
                                </div>
                                <div class="right-transaction-time" style="display: flex;align-items: end;margin-left: 10px;">
                                    <div class="btn btn-default" onclick="updateDateTransaction(this,{{$transaction->id}})">Cập nhập</div>
                                </div>
                            </div>
                        </div>

                        <div class="wrap-transaction-paper">
                            <div class="title-transaction-paper">Giấy tờ thuê xe
                            </div>
                            <div class="transaction-paper">
                                <div style="display: flex;align-items: center">
                                    <div class="wrap-svg">
                                        <svg width="17" height="16" viewBox="0 0 17 16" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M8.49967 1.33325C4.82634 1.33325 1.83301 4.32659 1.83301 7.99992C1.83301 11.6733 4.82634 14.6666 8.49967 14.6666C12.173 14.6666 15.1663 11.6733 15.1663 7.99992C15.1663 4.32659 12.173 1.33325 8.49967 1.33325ZM8.49967 6.05325C8.22634 6.05325 7.99967 5.83325 7.99967 5.55325C7.99967 5.27992 8.22634 5.05325 8.49967 5.05325C8.77967 5.05325 8.99967 5.27992 8.99967 5.55325C8.99967 5.83325 8.77967 6.05325 8.49967 6.05325ZM8.99967 10.3866C8.99967 10.6666 8.77301 10.8866 8.49967 10.8866C8.22634 10.8866 7.99967 10.6666 7.99967 10.3866V7.27992C7.99967 6.99992 8.22634 6.77992 8.49967 6.77992C8.77301 6.77992 8.99967 6.99992 8.99967 7.27992V10.3866Z"
                                                fill="#666666"></path>
                                        </svg>
                                    </div>
                                    <p class="font-12" style="padding-top: 5px">Chọn 1 trong 2 hình thức</p>
                                </div>
                                <div class="transaction-paper-top"><img src="admin/assets/images/gplx_cccd.png"
                                                                        style="width: 25px"> GPLX & CCCD gắn chip (đối
                                    chiếu)
                                </div>
                                <div class="transaction-paper-bottom"><img src="admin/assets/images/gplx_passport.png"
                                                                           style="width: 25px"> GPLX (đối chiếu) &
                                    Passport (giữ lại)
                                </div>
                            </div>
                        </div>

                        <div class="wrap-transaction-mortgage">
                            <div class="title-transaction-mortgage">Tài sản thế chấp
                            </div>
                            @if(!empty($transaction->car->mortgage))
                                <div class="transaction-mortgage">
                                    <div class="transaction-mortgage-top">{{$transaction->car->note_mortgage}}</div>
                                </div>
                            @endif
                        </div>
                        <div class="wrap-transaction-mortgage">
                            <div class="title-transaction-mortgage">Đánh giá</div>
                            @if(!empty($transaction->review) || !empty($transaction->review_customer))
                                @if(!empty($transaction->review))
                                    @php
                                        $customerReview = $transaction->review->customer;
                                        $src = !empty($customerReview->avatar) ? asset($customerReview->avatar) : asset('admin/assets/images/users/avatar-1.jpg');
                                    @endphp
                                    <div class="item-review">
                                        <div class="user-info">
                                            <div>
                                                <a href="{!! $src !!}" data-lightbox="customer-profile" class="pull-left">
                                                    <img class="img-circle" src="{!! $src !!}" alt="" style="width: 70px;height: 70px">
                                                </a>
                                            </div>
                                            <div class="info">
                                                <div class="info-name"><div>{{$customerReview->fullname}}</div> <div>Khách thuê</div></div>
                                                <div class="rate">
                                                    <div class="rating-star">{!! loadHtmlReviewStar($transaction->review->star) !!}</div>
                                                    <p class="time">{{_dthuan($transaction->review->created_at)}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content">
                                            {{$transaction->review->content}}
                                        </div>
                                        @if(count($transaction->review->review_content) > 0)
                                        <div class="content" style="display: flex">
                                            @foreach($transaction->review->review_content as $key => $value)
                                                <div style="border: 1px solid;border-radius: 10px;padding: 3px;margin-right: 5px;">{{$value->content}}</div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                @endif
                                @if(!empty($transaction->review_customer))
                                    @php
                                        $customerReview = $transaction->review_customer->customer_owner;
                                        $src = !empty($customerReview->avatar) ? asset($customerReview->avatar) : asset('admin/assets/images/users/avatar-1.jpg');
                                    @endphp
                                    <div class="item-review">
                                        <div class="user-info">
                                            <div>
                                                <a href="{!! $src !!}" data-lightbox="customer-profile" class="pull-left">
                                                    <img class="img-circle" src="{!! $src !!}" alt="" style="width: 70px;height: 70px">
                                                </a>
                                            </div>
                                            <div class="info">
                                                <div class="info-name"><div>{{$customerReview->fullname}}</div><div>Chủ xe</div></div>
                                                <div class="rate">
                                                    <div class="rating-star">{!! loadHtmlReviewStar($transaction->review_customer->star) !!}</div>
                                                    <p class="time">{{_dthuan($transaction->review_customer->created_at)}}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content">
                                            {{$transaction->review_customer->content}}
                                        </div>
                                        @if(count($transaction->review_customer->review_content) > 0)
                                        <div class="content" style="display: flex">
                                            @foreach($transaction->review_customer->review_content as $key => $value)
                                                <div style="border: 1px solid;border-radius: 10px;padding: 3px;margin-right: 5px;">{{$value->content}}</div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="transaction-mortgage">
                                    <div class="transaction-mortgage-top">Không có đánh giá</div>
                                </div>
                            @endif
                        </div>
                        <div class="title-transaction-time">Bảo hiểm thuê xe</div>
                        <div style="margin-bottom: 10px;margin-top: 5px;display: flex;justify-content: space-between;font-size: 15px">
                            <div>{!! !empty($transaction->transaction_insurance) && (!empty($transaction->transaction_insurance->certUrl)) ? '<a target="_blank" href="'.$transaction->transaction_insurance->certUrl.'"><img src="'.asset('admin/assets/images/attachments.svg').'"> Xem bảo hiểm</a>' : '' !!} </div>
                        </div>
                        <div class="wrap-transaction-mortgage">
                            <div class="title-transaction-mortgage">Hình ảnh chuyến
                            </div>
                            <div style="margin-bottom: 10px;margin-top: 5px;display: flex;justify-content: space-between;font-size: 15px">
                                <div>{!! count($transaction->image_transaction_all) > 0 ? '<a class="dt-modal2" href="admin/transaction/viewImageTransaction/'.$transaction->id.'"><img src="'.asset('admin/assets/images/attachments.svg').'"> Xem hình ảnh chuyến</a>' : '' !!} </div>
                            </div>
                        </div>
                        <div class="wrap-transaction-mortgage">
                            <div class="title-transaction-mortgage">Nhân viên CSKH</div>
                            @php
                                $htmlImage = '';
                               if (!empty($transaction->transaction_staff)){
                                   foreach ($transaction->transaction_staff as $key => $value){
                                       $url = !empty(($value['image'])) ? asset('storage/'.$value['image']) : asset('admin/assets/images/users/avatar-1.jpg');
                                       $htmlImage.= '<div style="width: 50px;" data-toggle="tooltip" data-placement="top" title="'.$value['name'].'">'.loadImageAvatar($url,'40px').'</div>';
                                   }
                               }
                            @endphp
                            <div style="display: flex;flex-wrap: wrap">{!! $htmlImage !!}</div>
                        </div>
                        <form id="comment-upload" action="admin/transaction/addComment" method="post">
                            {{csrf_field()}}
                            <label for="content">Bình luận</label>
                            <textarea class="form-control content" name="content" cols="3" rows="3"
                                      placeholder="Nhập nội dung..."></textarea>
                            <div class="dropzone needsclick" style="cursor: pointer;margin-top: 10px">
                                <div id="dropzone" class="transaction_comment">
                                    <div class="dz-message needsclick">
                                        Kéo thả tập tin và hình ảnh vào đây
                                    </div>
                                </div>
                                <div class="dropzone-previews"></div>
                            </div>
                            <div style="margin-top: 10px;text-align: right">
                                <button class="btn btn-default waves-effect waves-light"
                                        type="submit">Thêm bình luận
                                </button>
                            </div>
                        </form>
                        <div class="result_comment">
                            @forelse($transaction->comment as $key => $value)
                                @php
                                    $urlImage = !empty($value->user->image) ? asset('storage/'.$value->user->image) : 'admin/assets/images/users/avatar-1.jpg';
                                    $userName = !empty($value->user->name) ? $value->user->name : '';
                                    $fileComment = !empty($value->file) ? $value->file : [];
                                    $arrFileType = getFileType();
                                @endphp
                                <div class="comment">
                                    <img src="{{$urlImage}}" alt="" class="comment-avatar">
                                    <div class="comment-body">
                                        <div class="comment-text">
                                            <div class="comment-header">
                                                <a title="">{{$userName}}</a><span>{{getDiffForHumans($value['created_at'])}}</span>
                                            </div>
                                            <div class="edit_content_{{$value->id}} hide">
                                                <textarea class="form-control content_edit_{{$value->id}}" name="content_edit" cols="3" rows="3">{{$value['content']}}</textarea>
                                                <div style="margin-top: 10px;text-align: right">
                                                    <button class="btn btn-white waves-effect waves-light" onclick="cancelEdit({{$value->id}})">Hủy bỏ
                                                    </button>
                                                    <button class="btn btn-default waves-effect waves-light" onclick="submitEdit({{$value->id}})"
                                                            type="submit">Lưu lại
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="content_{{$value->id}}">{{$value['content']}}</div>
                                            <div class="m-t-15">
                                                @forelse($fileComment as $file)
                                                    @php
                                                        $file_name = explode('___',$file->name_file);
                                                    @endphp
                                                    @if(in_array($file['filetype'],$arrFileType))
                                                        @if(!empty($file->name_file))
                                                            <a href="{{asset('storage/'.$file->name_file)}}" data-lightbox="customer-profile" class="display-block mbot5">
                                                                <img src="{{asset('storage/'.$file->name_file)}}" class="thumb-md">
                                                            </a>
                                                        @endif
                                                    @else
                                                        <div style="margin-bottom: 5px;margin-top: 5px"><a target="_blank" href="{{asset('storage/'.$file->name_file)}}" >{{$file_name[1]}}</a></div>
                                                    @endif
                                                @empty
                                                @endforelse

                                            </div>
                                            <div style="position: absolute;right: 10px;top: 5px;display: flex;">
                                                {!! $value->user_id == get_staff_user_id() ? '<div class="cursor"><i class="fa fa-edit" onclick="editComment('.$value->id.')"></i></div>' : '' !!}
                                                <div class="cursor" style="color: red;margin-left: 5px"><a style="color: red" onclick="deleteComment({{$value->id}});"><i class="fa fa-remove"></i></a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="transaction-right">
                        <div class="contact-card"
                             style="display: flex;align-items: center;border-bottom: 2px solid #eee;padding-bottom: 10px">
                            @php
                                $src = !empty($transaction->customer->avatar) ? asset($transaction->customer->avatar) : asset('admin/assets/images/users/avatar-1.jpg');
                            @endphp
                            <a href="{{$src}}" data-lightbox="customer-profile" class="pull-left">
                                <img class="img-circle" src="{{$src}}" alt="" style="width: 70px;height: 70px">
                            </a>
                            <div class="member-info" style="padding-left: 0px;padding-bottom: 0px;padding-left: 20px">
                                <div class="text-dark"><small>Người thuê xe</small></div>
                                <h4 class="m-t-0 m-b-5">
                                    <b><a target="_blank" href="admin/clients/view/{{$transaction->customer->id}}" class="cursor" style="color: unset">{{!empty($transaction->customer->fullname) ? $transaction->customer->fullname : ''}}</a></b>
                                </h4>
                                <h4 class="m-t-0 m-b-5">
                                    <b><i class="fa fa-phone" aria-hidden="true"></i> <span class="phone_customer">{{$transaction->customer->phone}}</span></b>
                                </h4>
                                @if(!empty($transaction->customer->transaction_finish->count()))
                                    <div style="display: flex">
                                        {!! !empty($star_avg_renter) ? '<div><img style="width: 18px;height: 18px"
                                                  src="admin/assets/images/star.svg"> '.formatNumber($star_avg_renter).'</div>' : '' !!}
                                        <div style="margin-left: 10px"><img style="width: 18px;height: 18px"
                                                                            src="admin/assets/images/tick-circle.svg">{{$transaction->customer->transaction_finish->count()}}
                                            chuyến
                                        </div>
                                    </div>
                                @else
                                    <div style="display: flex;align-items: center">Chưa có chuyến</div>
                                @endif
                            </div>
                        </div>
                        <div class="contact-card"
                             style="display: flex;align-items: center;border-bottom: 2px solid #eee;padding-bottom: 20px;padding-top: 10px">
                            @php
                                $src = !empty($transaction->car->customer->avatar) ? asset($transaction->car->customer->avatar) : asset('admin/assets/images/users/avatar-1.jpg');
                                if ($transaction->type == 1){
                                    $review_car = 'review_car';
                                    $transaction_finish = 'transaction_finish';
                                } else {
                                    $review_car = 'review_car_talent';
                                    $transaction_finish = 'transaction_finish_talent';
                                }
                            @endphp
                            <a href="{{$src}}" data-lightbox="customer-profile" class="pull-left">
                                <img class="img-circle" src="{{$src}}" alt="" style="width: 70px;height: 70px">
                            </a>
                            <div class="member-info" style="padding-left: 0px;padding-bottom: 0px;padding-left: 20px">
                                <div class="text-dark"><small>Chủ xe</small></div>
                                <h4 class="m-t-0 m-b-5">
                                    <b><a target="_blank" href="admin/clients/view/{{$transaction->car->customer->id}}" class="cursor" style="color: unset">{{!empty($transaction->car->customer->fullname) ? $transaction->car->customer->fullname : ''}}</a></b>
                                </h4>
                                <h4 class="m-t-0 m-b-5">
                                    <b>{{!empty($transaction->car->customer->phone) ? $transaction->car->customer->phone : ''}}</b>
                                </h4>
                                @if(!empty($transaction->car->transaction_finish->count()))
                                    <div style="display: flex">
                                        {!! !empty($star_avg) ? '<div><img style="width: 18px;height: 18px"
                                                  src="admin/assets/images/star.svg"> '.formatNumber($star_avg).'</div>' : '' !!}
                                        <div style="margin-left: 10px"><img style="width: 18px;height: 18px" src="admin/assets/images/tick-circle.svg">{{$transaction->car->transaction_finish->count()}}
                                            chuyến
                                        </div>
                                    </div>
                                @else
                                    <div style="display: flex;align-items: center">Chưa có chuyến</div>
                                @endif
                            </div>
                        </div>
                        <div class="wrap-transaction-address">
                            <div class="title-transaction-address">Địa điểm giao xe</div>
                            <div class="title-address"><img src="admin/assets/images/location.svg"
                                                            style="width: 20px;margin-right: 5px">{{$transaction->car->district->name.','.$transaction->car->province->name}}
                            </div>
                        </div>
                        <div class="wrap-transaction-price">
                            <div class="title-transaction-price">Thanh toán cọc</div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Phiếu thu</div>
                                <div class="value-price">{{count($transaction->paymentDeposit) > 0 ? ($transaction->paymentDeposit[0]->reference_no) : ''}}</div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Tổng tiền</div>
                                <div class="value-price">{{formatMoney($transaction->paymentDeposit->sum('payment'))}}đ</div>
                            </div>
                            @php
                                $dtImage = null;
                                if (count($transaction->paymentDeposit) > 0){
                                   $dtImage = !empty($transaction->paymentDeposit[0]->payment_mode->image) ? asset('storage/'.$transaction->paymentDeposit[0]->payment_mode->image) : null;
                                }
                            @endphp
                            <div class="detail-transaction-price border_bottom" style="display: flex;align-items: center">
                                <div class="title-price">Phương thức thanh toán</div>
                                <div class="value-price" style="display: flex;align-items: center">{!! count($transaction->paymentDeposit) > 0 ? loadImage($dtImage). $transaction->paymentDeposit[0]->payment_mode->name : '' !!}</div>
                            </div>
                        </div>
                        @if($transaction->type == 2)
                        <div class="wrap-transaction-price">
                            <div class="title-transaction-price">Thông tin lộ trình</div>
                            @forelse($transaction->route as $key => $value)
                                @php
                                    $link = 'https://api.map4d.vn/sdk/v2/geocode?key='.get_option('key_map4d').'&location='.$value->lat_start.','.$value->lng_start.'';
                                    $dataCurl =  GetCurlData($link);
                                    $dataCurl = json_decode($dataCurl);
                                @endphp

                                <div class="detail-transaction-price">
                                    <div class="title-price">{{!empty($dataCurl) && !empty($dataCurl->result[0]) ? $dataCurl->result[0]->address : ''}}</div>
                                </div>
                            @empty
                            @endforelse
                            <div class="detail-transaction-price">
                                <div class="title-price">Tổng lộ trình
                                </div>
                                <div class="value-price">{{formatNumber($transaction->total_route)}} km</div>
                            </div>
                        </div>
                        @endif
                        @php
                            $depoist = ($transaction->grand_total * get_option('percent_deposit') / 100);
                        @endphp
                        <div class="wrap-transaction-price">
                            <div class="title-transaction-price">Bảng tính giá</div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Đơn giá thuê
                                </div>
                                <div
                                    class="value-price">{{formatMoney($transaction->price + $transaction->price_service )}}
                                    đ/ ngày
                                </div>
                            </div>
                            <div class="detail-transaction-price border_bottom">
                                <div class="title-price">Bảo hiểm thuê xe
                                </div>
                                <div class="value-price">{{formatMoney($transaction->price_insurance)}}đ/ ngày</div>
                            </div>
                            <div class="detail-transaction-price border_bottom">
                                <div class="title-price">Tổng cộng</div>
                                <div class="value-price">{{formatMoney($transaction->total)}}đ
                                    x {{$transaction->quantity}} ngày
                                </div>
                            </div>
                            <div class="detail-transaction-price border_bottom">
                                <div class="title-price">Phí đưa đón tận nơi</div>
                                <div class="value-price">{{formatMoney($transaction->amount_km)}}
                                </div>
                            </div>
                            <div class="detail-transaction-price border_bottom border_bottom">
                                <div class="title-price">
                                    <div class="wrap-svg">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M21.3 10.8394C21.69 10.8394 22 10.5294 22 10.1394V9.20938C22 5.10938 20.75 3.85938 16.65 3.85938H7.35C3.25 3.85937 2 5.10938 2 9.20938V9.67938C2 10.0694 2.31 10.3794 2.7 10.3794C3.6 10.3794 4.33 11.1094 4.33 12.0094C4.33 12.9094 3.6 13.6294 2.7 13.6294C2.31 13.6294 2 13.9394 2 14.3294V14.7994C2 18.8994 3.25 20.1494 7.35 20.1494H16.65C20.75 20.1494 22 18.8994 22 14.7994C22 14.4094 21.69 14.0994 21.3 14.0994C20.4 14.0994 19.67 13.3694 19.67 12.4694C19.67 11.5694 20.4 10.8394 21.3 10.8394ZM9 8.87938C9.55 8.87938 10 9.32938 10 9.87938C10 10.4294 9.56 10.8794 9 10.8794C8.45 10.8794 8 10.4294 8 9.87938C8 9.32938 8.44 8.87938 9 8.87938ZM15 15.8794C14.44 15.8794 13.99 15.4294 13.99 14.8794C13.99 14.3294 14.44 13.8794 14.99 13.8794C15.54 13.8794 15.99 14.3294 15.99 14.8794C15.99 15.4294 15.56 15.8794 15 15.8794ZM15.9 9.47937L9.17 16.2094C9.02 16.3594 8.83 16.4294 8.64 16.4294C8.45 16.4294 8.26 16.3594 8.11 16.2094C7.82 15.9194 7.82 15.4394 8.11 15.1494L14.84 8.41938C15.13 8.12938 15.61 8.12938 15.9 8.41938C16.19 8.70938 16.19 9.18937 15.9 9.47937Z"
                                                fill="#f26a2b"></path>
                                        </svg>
                                    </div>
                                    Chương trình giảm giá
                                </div>
                                <div
                                    class="value-price">{{!empty($transaction->promotion) ? '-'.formatMoney($transaction->promotion) : 0}}
                                    đ
                                </div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price">Thành tiền</div>
                                <div class="value-price">{{formatMoney($transaction->grand_total)}}đ</div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price text_bold" style="color: black">Đặt cọc qua ứng dụng
                                </div>
                                <div class="value-price"
                                     style="color: #5fcf86 !important;">{{formatMoney($depoist)}}
                                    đ
                                </div>
                            </div>
                            <div class="detail-transaction-price">
                                <div class="title-price text_bold" style="color: black">Thanh toán khi nhận xe
                                </div>
                                <div class="value-price"
                                     style="color: #5fcf86 !important;">{{formatMoney($transaction->grand_total - $depoist)}}
                                    đ
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="transaction-right-bottom">
                        <div class="wrap-transaction-price">
                                <div class="title-transaction-price">Bảng tính giá nội bộ</div>
                                <div class="detail-transaction-price">
                                    <div class="title-price">Đơn giá thuê
                                    </div>
                                    <div class="value-price">{{formatMoney($transaction->price)}}đ/ ngày</div>
                                </div>
                                <div class="detail-transaction-price">
                                    <div class="title-price">Phí dịch vụ ({!! $transaction->percent_service.' %' !!})
                                    </div>
                                    <div class="value-price">{{formatMoney($transaction->price_service )}}đ/ ngày</div>
                                </div>
                                <div class="detail-transaction-price border_bottom">
                                    <div class="title-price">Bảo hiểm thuê xe
                                    </div>
                                    <div class="value-price">{{formatMoney($transaction->price_insurance)}}đ/ ngày</div>
                                </div>
                                <div class="detail-transaction-price border_bottom">
                                    <div class="title-price">Tổng cộng</div>
                                    <div class="value-price">{{formatMoney($transaction->total)}}đ
                                        x {{$transaction->quantity}} ngày
                                    </div>
                                </div>
                                <div class="detail-transaction-price border_bottom">
                                    <div class="title-price">Phí đưa đón tận nơi</div>
                                    <div class="value-price">{{formatMoney($transaction->amount_km)}}
                                    </div>
                                </div>
                                <div class="detail-transaction-price border_bottom border_bottom">
                                    <div class="title-price">
                                        <div class="wrap-svg">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M21.3 10.8394C21.69 10.8394 22 10.5294 22 10.1394V9.20938C22 5.10938 20.75 3.85938 16.65 3.85938H7.35C3.25 3.85937 2 5.10938 2 9.20938V9.67938C2 10.0694 2.31 10.3794 2.7 10.3794C3.6 10.3794 4.33 11.1094 4.33 12.0094C4.33 12.9094 3.6 13.6294 2.7 13.6294C2.31 13.6294 2 13.9394 2 14.3294V14.7994C2 18.8994 3.25 20.1494 7.35 20.1494H16.65C20.75 20.1494 22 18.8994 22 14.7994C22 14.4094 21.69 14.0994 21.3 14.0994C20.4 14.0994 19.67 13.3694 19.67 12.4694C19.67 11.5694 20.4 10.8394 21.3 10.8394ZM9 8.87938C9.55 8.87938 10 9.32938 10 9.87938C10 10.4294 9.56 10.8794 9 10.8794C8.45 10.8794 8 10.4294 8 9.87938C8 9.32938 8.44 8.87938 9 8.87938ZM15 15.8794C14.44 15.8794 13.99 15.4294 13.99 14.8794C13.99 14.3294 14.44 13.8794 14.99 13.8794C15.54 13.8794 15.99 14.3294 15.99 14.8794C15.99 15.4294 15.56 15.8794 15 15.8794ZM15.9 9.47937L9.17 16.2094C9.02 16.3594 8.83 16.4294 8.64 16.4294C8.45 16.4294 8.26 16.3594 8.11 16.2094C7.82 15.9194 7.82 15.4394 8.11 15.1494L14.84 8.41938C15.13 8.12938 15.61 8.12938 15.9 8.41938C16.19 8.70938 16.19 9.18937 15.9 9.47937Z"
                                                    fill="#f26a2b"></path>
                                            </svg>
                                        </div>
                                        Chương trình giảm giá
                                    </div>
                                    <div
                                        class="value-price">{{!empty($transaction->promotion) ? '-'.formatMoney($transaction->promotion) : 0}}
                                        đ
                                    </div>
                                </div>
                                <div class="detail-transaction-price">
                                    <div class="title-price">Thành tiền</div>
                                    <div class="value-price">{{formatMoney($transaction->grand_total)}}đ</div>
                                </div>
                                <div class="detail-transaction-price">
                                    <div class="title-price text_bold" style="color: black">Đặt cọc qua ứng dụng
                                    </div>
                                    <div class="value-price"
                                         style="color: #5fcf86 !important;">{{formatMoney($depoist)}}
                                        đ
                                    </div>
                                </div>
                                <div class="detail-transaction-price">
                                    @php
                                        $payment = $transaction->grand_total - $depoist;
                                        $refund = $transaction->revenue_customer;
                                    @endphp
                                    <div class="title-price text_bold" style="color: black">Thanh toán khi nhận xe
                                    </div>
                                    <div class="value-price" style="color: #5fcf86 !important;">{{formatMoney($payment)}}đ
                                    </div>
                                </div>
                                <div class="detail-transaction-price">
                                    <div class="title-price text_bold" style="color: black">Tiền chủ xe</div>
                                    <div class="value-price">{{formatMoney($refund)}}đ</div>
                                </div>
                                <div class="detail-transaction-price">
                                    <div class="title-price text_bold" style="color: black">Tiền hoàn chủ xe</div>
                                    <div class="value-price" style="color: red">{{formatMoney($refund - $payment)}}đ</div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a class="dt-modal hide click1"
               href="admin/transaction/view/{{$transaction->id}}?type={{$transaction->type}}" data-toggle="modal"
               data-target="#myModal"></a>
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
</div>
<script>
    $('.datetimepickerNew').datetimepicker({
        format:'d/m/Y H:i',
    });
    expenseDropzone = initDropzone('.transaction_comment', '#comment-upload', {
        previewTemplate: $("#preview-template").html(),
        autoProcessQueue: false,
        addRemoveLinks: true,
        previewsContainer: '.dropzone-previews',
        clickable: '.transaction_comment',
        sending: function (file, xhr, formData) {

        },
        success: function (file, response) {
            if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
            }
        }
    });

    $("#comment-upload").validate({
        rules: {},
        messages: {},
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
            formData.append('transaction_id', {{$transaction->id}});
            $.each(expenseDropzone.files, function (index, value) {
                formData.append('file[]', value);
            })

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
                        $(".content").val(' ');
                        $(".dropzone-previews").html(' ');
                        expenseDropzone.files = [];
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    $(".result_comment").html(data.html);
                })
                .fail(function (err) {
                });
            return false;
        }
    });

    function cancelEdit(comment_id){
        $(`.edit_content_${comment_id}`).addClass('hide');
        $(`.content_${comment_id}`).removeClass('hide');
    }

    function editComment(comment_id){
        $(`.edit_content_${comment_id}`).removeClass('hide');
        $(`.content_${comment_id}`).addClass('hide');
    }
    function submitEdit(comment_id){
        content = $(`.content_edit_${comment_id}`).val();
        $.ajax({
            url: 'admin/transaction/updateComment',
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            data: {
                comment_id: comment_id,
                content: content
            },
        })
            .done(function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                $(".result_comment").html(data.html);
            })
            .fail(function () {

            });
        return false;
    }
    function deleteComment(comment_id){
        var r = confirm("Bạn có chắc muốn xóa không ?");
        if (r == false) {
            return false;
        } else {
            $.ajax({
                url: 'admin/transaction/deleteComment',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    comment_id: comment_id,
                    transaction_id: {{$transaction->id}}
                },
            })
                .done(function (data) {
                    if (data.result) {
                        alert_float('success', data.message);
                    } else {
                        alert_float('error', data.message);
                    }
                    $(".result_comment").html(data.html);
                })
                .fail(function () {

                });
            return false;
        }
    }

    $(".value_start-right").change(function () {
        date_end = $(this).val();
        $(".date_end").val(date_end);
    })

    function updateDateTransaction(_this,transaction_id){
        $.ajax({
            url: 'admin/transaction/updateDateTransaction',
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            data: {
                date_end: $(".date_end").val(),
                transaction_id: transaction_id
            },
        })
            .done(function (data) {
                $('.click1')[0].click();
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
            })
            .fail(function () {

            });
        return false;
    }
</script>
