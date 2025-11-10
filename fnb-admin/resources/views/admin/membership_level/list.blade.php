@extends('admin.layouts.index')
@section('content')
    <style>

        .content {
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .section {
            background: #fff;
            padding: 5px;
            padding-left: 25px;
            border-left: 4px solid #FF5A1F;
        }

        .section h2 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }

        .section h3 {
            color: #34495e;
            font-size: 16px;
            margin: 15px 0 10px 0;
            font-weight: bold;
        }

        .section p {
            margin-bottom: 12px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: linear-gradient(135deg, #ff5a1f, #f25c28);
            color: white;
            font-weight: bold;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e3f2fd;
            transition: background-color 0.3s ease;
        }

        .highlight {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .note {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-style: italic;
        }

        ul {
            margin-left: 10px;
            margin-bottom: 15px;
        }

        li {
            margin-bottom: 8px;
        }

        .full-width {
            width: 100%;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .content {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }

            .two-column {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-excellent {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .badge-good {
            background-color: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }

        .badge-average {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .badge-poor {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
        }

        .subsection {
            margin-bottom: 30px;
        }

        .subsection-title {
            font-size: 16px;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 15px;
        }

        .criteria-list {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }

        .criteria-list p {
            margin-bottom: 10px;
            font-size: 15px;
        }

        .criteria-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .check-icon {
            width: 16px;
            height: 16px;
            background: #28a745;
            border-radius: 3px;
            margin-right: 10px;
            position: relative;
        }

        .check-icon::after {
            content: '✓';
            color: white;
            font-size: 12px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
    <style>
        .input-num {
            border-top:0px;
            border-left:0px;
            border-right:0px;
            text-align: center;
            padding: 5px;
            width: 150px;
        }
        .p-l-20 {
            padding-left: 40px;
        }
        .input-num:read-only {
            border: 0px!important;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('c_membership_level')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/membership_level/list">{{lang('c_membership_level')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs" style="margin-left: unset">
                <li class="active">
                    <a href="#info" data-toggle="tab" aria-expanded="false">
                        <span class="visible-xs"><i class="fa fa-home"></i></span>
                        <span class="hidden-xs">Công thức</span>
                    </a>
                </li>
                <li>
                    <a href="#note" data-toggle="tab" aria-expanded="true">
                        <span class="visible-xs"><i class="fa fa-user"></i></span>
                        <span class="hidden-xs">Cách thực hiện</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div class="card-box">
                        <form action="admin/membership_level/updateMember" method="post" id="formMemberShip" data-parsley-validate
                              novalidate
                              enctype="multipart/form-data">
                            {{csrf_field()}}
                            <div class="header">
                                <h3 class="text-center">QUY ĐỊNH XẾP HẠNG THÀNH VIÊN HỆ THỐNG</h3>
                            </div>
                            <div class="content">
                                <!-- Phần 1: CÁC TIÊU CHÍ TÍNH ĐIỂM -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">1. CÁC TIÊU CHÍ TÍNH ĐIỂM</h4>
                                    <div class="p-l-20">
                                        <p>Để khuyến khích khách hàng chi tiêu và gắn bó lâu dài, điểm thưởng được tính dựa trên 4 tiêu chí:</p>
                                        <h4>1.1. Chi tiêu trong quý</h4>
                                        <p><span class="checkmark">✅</span> <strong>Ý nghĩa:</strong> Tăng thưởng theo tổng số tiền thanh toán trong quý.</p>
                                        <p><span class="checkmark">✅</span> <strong>Quy tắc tính:</strong> Mức chi tiêu càng cao, điểm càng nhiều.</p>
                                        <p><span class="checkmark">✅</span> <strong>Khuyến khích thanh toán:</strong> Khách hàng được tham gia quay thưởng định kỳ</p>

                                        <table>
                                            <thead>
                                            <tr>
                                                <th>Mức chi tiêu trong quý</th>
                                                <th>Điểm thưởng</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!empty($membership_expense))
                                                @php
                                                    $keySTT = 0;
                                                @endphp
                                                @foreach($membership_expense as $key => $value)
                                                    <tr>
                                                        <td>Từ
                                                            <input data-key="membership_expense-{{$keySTT}}-money_start" name="membership_expense[{{$value->id}}][money_start]"
                                                                   type="text"
                                                                   class="input-num"
                                                                   {{$keySTT > 0 ? 'readonly' : ''}}
                                                                   value="{{number_format($value->money_start)}}"/>
                                                            @if(count($membership_expense) - 1 == $key)
                                                                VNĐ Trở lên
                                                                @if(is_numeric($value->point_end))
                                                                    <input name="membership_expense[{{$value->id}}][money_end]" type="hidden" class="input-num" value="{{number_format($value->money_end)}}"/>
                                                                @endif
                                                            @else
                                                                → dưới
                                                                <input
                                                                    data-key="membership_expense-{{$keySTT}}-money_end"
                                                                    name="membership_expense[{{$value->id}}][money_end]"
                                                                    type="text"
                                                                    class="input-num"
                                                                    onchange="changeEnd(this, {{$keySTT}}, 'membership_expense', 'money_start')"
                                                                    value="{{number_format($value->money_end)}}"/>
                                                                VNĐ
                                                            @endif

                                                        </td>
                                                        <td class="text-center">
                                                            <input name="membership_expense[{{$value->id}}][point]" type="text" class="input-num" value="{{number_format($value->point)}}"/>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $keySTT++;
                                                    @endphp
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>

                                        <div class="note">
                                            <strong>Ghi chú:</strong> Mức chi tiêu cộng dồn theo quý. Ví dụ: tháng 1 mua 5 triệu + tháng 2 mua 4 triệu + tháng 3 mua 7 triệu = 16 triệu → <strong>151 điểm</strong>.
                                        </div>

                                        <h4>1.2. Số lần mua hàng trong quý</h4>
                                        <p><span class="checkmark">✅</span> <strong>Ý nghĩa:</strong> Khuyến khích mua sắm đều đặn, không chỉ tập trung một lần.</p>

                                        <table>
                                            <thead>
                                            <tr>
                                                <th>Số lần mua hàng trong quý</th>
                                                <th>Điểm thưởng</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($membership_purchases))
                                                    @php
                                                        $keySTT = 0;
                                                    @endphp
                                                    @foreach($membership_purchases as $key => $value)
                                                        @php
                                                            if(empty($value->number_purchases_start)) continue;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                @if(count($membership_purchases) - 1 == $key)
                                                                    Từ
                                                                @endif
                                                                    <input
                                                                        data-key="membership_purchases-{{$keySTT}}-number_purchases_start"
                                                                        name="membership_purchases[{{$value->id}}][number_purchases_start]"
                                                                        type="text"
                                                                        class="input-num"
                                                                        {{$keySTT > 0 ? 'readonly' : ''}}
                                                                        value="{{number_format($value->number_purchases_start)}}"/>
                                                                @if(count($membership_purchases) - 1 == $key)
                                                                    Lần trở lên
                                                                    @if(is_numeric($value->number_purchases_end))
                                                                        <input name="membership_purchases[{{$value->id}}][number_purchases_end]" type="hidden" class="input-num" value="{{number_format($value->number_purchases_end)}}"/>
                                                                    @endif
                                                                @else
                                                                    →
                                                                    <input
                                                                        data-key="membership_purchases-{{$keySTT}}-number_purchases_end"
                                                                        onchange="changeEnd(this, {{$keySTT}}, 'membership_purchases', 'number_purchases_start', true)"
                                                                        name="membership_purchases[{{$value->id}}][number_purchases_end]"
                                                                        type="text" class="input-num"
                                                                        value="{{number_format($value->number_purchases_end - 1)}}"/>
                                                                    lần
                                                                @endif
                                                            </td>
                                                            <td class="text-center"><input name="membership_purchases[{{$value->id}}][point]" type="text" class="input-num" value="{{number_format($value->point)}}"/></td>
                                                        </tr>
                                                        @php
                                                            $keySTT++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>

                                        <div class="note">
                                            <strong>Ghi chú:</strong> Dù chi tiêu ít nhưng mua nhiều lần vẫn tích được điểm.
                                        </div>

                                        <h4>1.3. Thời gian gắn bó</h4>
                                        <p><span class="checkmark">✅</span> <strong>Ý nghĩa:</strong> Tôn vinh thành viên lâu năm.</p>

                                        <table>
                                            <thead>
                                            <tr>
                                                <th>Thời gian là thành viên</th>
                                                <th>Điểm thưởng</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!empty($membership_long_term))
                                                @php
                                                    $keySTT = 0;
                                                @endphp
                                                @foreach($membership_long_term as $key => $value)
                                                    <tr>
                                                        <td>
                                                            @if(count($membership_long_term) - 1 == $key)
                                                                Từ
                                                            @endif
                                                            <input
                                                                data-key="membership_long_term-{{$keySTT}}-month_start"
                                                                {{$keySTT > 0 ? 'readonly' : ''}}
                                                                name="membership_long_term[{{$value->id}}][month_start]"
                                                                type="text" class="input-num"
                                                                value="{{number_format($value->month_start)}}"/>
                                                            @if(count($membership_long_term) - 1 == $key)
                                                                Tháng Trở lên
                                                                @if(is_numeric($value->month_end))
                                                                    <input name="membership_long_term[{{$value->id}}][month_end]" type="hidden" class="input-num" value="{{number_format($value->month_end)}}"/>
                                                                @endif
                                                            @else
                                                                → <input
                                                                        data-key="membership_long_term-{{$keySTT}}-month_end"
                                                                        onchange="changeEnd(this, {{$keySTT}}, 'membership_long_term', 'month_start')"
                                                                        name="membership_long_term[{{$value->id}}][month_end]"
                                                                        type="text"
                                                                        class="input-num"
                                                                        value="{{number_format($value->month_end)}}"/> Tháng
                                                            @endif

                                                        </td>
                                                        <td class="text-center"><input name="membership_long_term[{{$value->id}}][point]" type="text" class="input-num" value="{{number_format($value->point)}}"/></td>
                                                    </tr>
                                                    @php
                                                        $keySTT++;
                                                    @endphp
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>

                                        <div class="note">
                                            <strong>Ghi chú:</strong> Tính từ ngày đăng ký thành viên.
                                        </div>

                                        <h4>1.4. Giới thiệu bạn bè</h4>
                                        <p><span class="checkmark">✅</span> <strong>Ý nghĩa:</strong> Khuyến khích giới thiệu khách mới.</p>
                                        <p><span class="checkmark">✅</span> <strong>Điểm thưởng:</strong></p>
                                        <p>· Mỗi khách mới giới thiệu thành công: <strong>30 điểm</strong></p>

                                        <div class="note">
                                            <strong>Ghi chú:</strong> Khách mới phải hoàn tất ít nhất 1 giao dịch thành công.
                                        </div>
                                    </div>
                                </div>

                                <!-- Phần 2: CÔNG THỨC TÍNH TỔNG ĐIỂM -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">2. CÔNG THỨC TÍNH TỔNG ĐIỂM</h4>
                                    <p>Tổng điểm tích lũy mỗi quý được tính như sau:</p>
                                    <div class="p-l-20">
                                        <div class="formula-box">
                                            <strong>Tổng điểm = </strong>Điểm chi tiêu + Điểm số lần mua hàng + Điểm thời gian gắn bó + Điểm giới thiệu bạn bè
                                        </div>
                                    </div>
                                </div>

                                <!-- Phần 3: BẢNG PHÂN HẠNG THÀNH VIÊN -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">3. BẢNG PHÂN HẠNG THÀNH VIÊN</h4>
                                    <p><strong>Hạng thành viên</strong> giúp phân loại ưu đãi & hạn mức:</p>
                                    <div class="p-l-20">
                                        <table>
                                            <thead>
                                            <tr>
                                                <th>Tổng điểm tích lũy trong quý</th>
                                                <th>Hạng thành viên</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!empty($membership_level))
                                                @php
                                                    $keySTT = 0;
                                                @endphp
                                                @foreach($membership_level as $key => $value)
                                                    <tr>
                                                        <td>
                                                            @if(count($membership_level) - 1 == $key)
                                                                Từ
                                                            @endif
                                                            <input
                                                                data-key="membership_level-{{$keySTT}}-point_start"
                                                                {{$keySTT > 0 ? 'readonly' : ''}}
                                                                name="membership_level[{{$value->id}}][point_start]"
                                                                type="text" class="input-num"
                                                                value="{{number_format($value->point_start)}}"/>
                                                            @if(count($membership_level) - 1 == $key)
                                                                Điểm Trở lên
                                                                @if(is_numeric($value->point_end))
                                                                    <input name="membership_level[{{$value->id}}][point_end]" type="hidden" class="input-num" value="{{number_format($value->point_end)}}"/>
                                                                @endif
                                                            @else
                                                            → <input
                                                                    data-key="membership_level-{{$keySTT}}-point_end"
                                                                    onchange="changeEnd(this, {{$keySTT}}, 'membership_level', 'point_start', true)"
                                                                    name="membership_level[{{$value->id}}][point_end]"
                                                                    type="text" class="input-num"
                                                                    value="{{number_format($value->point_end - 1)}}"/>
                                                            @endif
                                                        </td>
                                                        <td class="text-center"><strong>{{$value->name}}</strong></td>
                                                    </tr>
                                                    @php
                                                        $keySTT++;
                                                    @endphp
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>

                                        <div class="highlight">
                                            <p><strong>Ghi chú:</strong></p>
                                            <ul>
                                                <li>Giám đốc Vùng được giao quyền phê duyệt tăng hạng thành viên từ 1A lên 3A đối với khách hàng tiềm năng theo đề xuất của Giám đốc kinh doanh.</li>
                                                <li><strong>Đối với khách hàng VIP:</strong>
                                                    <ul>
                                                        <li>Ngoài số điểm vượt trên 200 theo quy định, khách hàng phải thoả mãn các quy định riêng của tiêu chuẩn VIP theo từng thời kỳ. Việc phê duyệt khách hàng VIP sẽ do Giám đốc phê duyệt theo đề xuất của Giám đốc vùng.</li>
                                                        <li>Khách hàng VIP: Sẽ được cấp Hạn mức thẻ Công nợ tối đa 50 triệu đồng</li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phần 4: VÍ DỤ TÍNH ĐIỂM CHI TIẾT -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">4. VÍ DỤ TÍNH ĐIỂM CHI TIẾT</h4>
                                    <div class="p-l-20">
                                        <div class="example-box">
                                            <p><strong>Ví dụ khách A:</strong></p>
                                            <ul>
                                                <li>Chi tiêu quý: <strong>12 triệu VNĐ</strong> → 86 điểm</li>
                                                <li>Số lần mua hàng: <strong>7 lần</strong> → 40 điểm</li>
                                                <li>Thời gian gắn bó: <strong>10 tháng</strong> → 25 điểm</li>
                                                <li>Giới thiệu bạn bè: <strong>1 người</strong> → 30 điểm</li>
                                            </ul>
                                            <p><strong>Tổng điểm = 86 + 40 + 25 + 30 = 181 điểm</strong></p>
                                            <p><strong>Xếp hạng: 3A</strong> <span class="status-badge badge-3a">Vàng</span></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phần 5: QUY ĐỊNH CHẾ TÀI & HẠN MỨC -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">5. QUY ĐỊNH CHẾ TÀI & HẠN MỨC</h4>
                                    <p>Để duy trì hạng, thành viên cần đáp ứng điều kiện sau:</p>
                                    <div class="p-l-20">
                                        <h4>5.1. Mức chi tiêu bình quân theo quý</h4>
                                        <ul>
                                            <li><strong>Yêu cầu:</strong> Tối thiểu <strong>3.000.000 VNĐ/quý</strong></li>
                                            <li><strong>Chế tài:</strong> Không đạt → <strong>Giảm hạng 1 bậc quý tiếp theo</strong></li>
                                        </ul>

                                        <h4>5.2. Mức chi tiêu bình quân 6 tháng</h4>
                                        <ul>
                                            <li><strong>Yêu cầu:</strong> Bình quân tối thiểu <strong>1.800.000 VNĐ/6 tháng</strong></li>
                                            <li><strong>Chế tài:</strong> Không đạt → <strong>Xem xét không gia hạn thành viên</strong></li>
                                        </ul>

                                        <h4>5.3. Hạn mức hóa đơn theo hạng</h4>
                                        <table>
                                            <thead>
                                            <tr>
                                                <th>Hạng thành viên</th>
                                                <th>Hạn mức hóa đơn mỗi lần thanh toán</th>
                                                <th>Chiết khấu</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(!empty($membership_level))
                                                @foreach($membership_level as $key => $value)
                                                    <tr>
                                                        <td class="text-center"><strong>{{$value->name}}</strong></td>
                                                        <td class="text-center">
                                                            @if(count($membership_level) - 1 == $key)
                                                                Không giới hạn – có chiết khấu riêng
                                                                @if(is_numeric($value->invoice_limit))
                                                                    <input name="membership_level[{{$value->id}}][invoice_limit]" type="hidden" class="input-num" value="{{number_format($value->invoice_limit)}}"/>
                                                                @endif
                                                            @else
                                                                Dưới <input name="membership_level[{{$value->id}}][invoice_limit]" type="text" class="input-num" value="{{number_format($value->invoice_limit)}}"/> VNĐ
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if(count($membership_level) - 1 == $key)
                                                                Có chiết khấu riêng
                                                                @if(is_numeric($value->radio_discount))
                                                                    <input name="membership_level[{{$value->id}}][radio_discount]" type="hidden" class="input-num" value="{{number_format($value->radio_discount)}}"/>
                                                                @endif
                                                            @else
                                                                <input name="membership_level[{{$value->id}}][radio_discount]" type="text" class="input-num" value="{{number_format($value->radio_discount)}}"/> %
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>

                                        <div class="highlight">
                                            <p><strong>Xử lý vượt hạn mức:</strong></p>
                                            <ul>
                                                <li><strong>Trường hợp 1:</strong> Chiết khấu theo quy định, riêng phần tiền vượt hạn mức không được chiết khấu</li>
                                                <li><strong>Trường hợp 2:</strong> Để được chiết khấu toàn bộ hóa đơn vượt hạn mức, khách hàng thành viên sẽ trao đổi trước với đơn vị kinh doanh khi sử dụng dịch vụ.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phần 6: GHI CHÚ QUAN TRỌNG -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">6. GHI CHÚ QUAN TRỌNG</h4>
                                    <div class="p-l-20">
                                        <p><span class="checkmark">✅</span> Mức chi tiêu tính trên <strong>tổng giá trị thanh toán thành công</strong></p>
                                        <p><span class="checkmark">✅</span> Thành viên nên <strong>chủ động theo dõi điểm số & hạng</strong> qua ứng dụng</p>
                                    </div>
                                </div>

                                <!-- Phần 7: TÍNH TOÁN TỰ ĐỘNG -->
                                <div class="col-md-8 col-md-offset-2">
                                    <h4 class="section">7. TÍNH TOÁN TỰ ĐỘNG</h4>
                                    <div class="p-l-20">
                                        <p><span class="checkmark">✅</span> Hệ thống <strong>tự động cộng điểm và xét hạng mỗi quý</strong></p>
                                        <p><span class="checkmark">✅</span> Thông báo gửi qua <strong>email hoặc ứng dụng thành viên</strong></p>
                                    </div>
                                </div>
                            </div>
                            <hr/>
                            <div class="form-group text-right m-b-0">
                                <button class="btn btn-primary waves-effect waves-light" type="submit">
                                    {{lang('dt_save')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane" id="note">
                    <div>
                        <div class="subsection">
                            <div class="criteria-list">
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Công thức tính và chạy hàm hạng thành viên theo quý để xét hạng thành viên. Thời gian chạy sẽ như sau.</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Thời gian sẽ chạy vào 1h sáng các ngày  (01/04, 01/07, 01/10, 01/01)</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Khi chạy xong công thức mỗi quý sẽ reset điểm về 0</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Khi chạy xong nếu thành viên nào tăng hạng sẽ gửi thông báo + popup chúc mừng về trên app</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Thành viên nào rớt hạng hoặc giữ hạng sẽ gửi thông báo về sau thời gian để trong cài đặt (Cài đặt -> Khác -> Số ngày gửi thông báo khi rớt hạng hoặc giữ hạng thành viên). Thay đổi số ngày gửi và nội dung gửi ở đây</span>
                                </div>
                            </div>

                            <div class="subsection-title">1.1 Chi tiêu trong quý</div>

                            <div class="criteria-list">
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Điều kiện lấy sẽ như sau. Lấy tổng tiền trên tất cả phiếu thu đã thanh toán trên phần mềm (Giao dịch -> Phiếu thu) trong quý của thành viên để xét theo tiêu chi bên công thức.</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span style="color:#ff5a1f">Chạy định kỳ vào mỗi quý.</span>
                                </div>
                            </div>


                            <div class="subsection-title">1.2 Số lần mua hàng trong quý</div>

                            <div class="criteria-list">
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Điều kiện lấy sẽ như sau. Đếm số phiếu phiếu thu đã thanh toán trên phần mềm (Giao dịch -> Phiếu thu) trong quý của thành viên để xét theo tiêu chi bên công thức.</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span style="color:#ff5a1f">Chạy định kỳ vào mỗi quý.</span>
                                </div>
                            </div>

                            <div class="subsection-title">1.3 Thời gian gắn bó</div>

                            <div class="criteria-list">
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Điều kiện lấy sẽ như sau. Lấy trên thông tin của thành viên trên phần mềm (Thành viên -> Danh sách thành viên). Dựa vào ngày đăng ký thành viên tính đến ngày chạy công thức xem đã tham gia được bao nhiêu tháng để tính công thức.</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span style="color:#ff5a1f">Chạy định kỳ vào mỗi quý.</span>
                                </div>
                            </div>

                            <div class="subsection-title">1.4 Giới thiệu bạn bè</div>

                            <div class="criteria-list">
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Điều kiện lấy sẽ như sau. Dựa vào mã giới thiệu của thành viên để xác minh. Khi giới thiệu và đăng ký thành viên -> Đăng ký thành viên xong phải phát sinh ít nhất 1 phiếu thu đã thanh toán để được tính điểm là giới thiệu bạn bè.</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span style="color:#ff5a1f">Mỗi lần giới thiệu sẽ được cộng 30 ngay lập tức khi hoàn tất.</span>
                                </div>
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
        $("#formMemberShip").validate({
            rules: {
                phone: {
                    required: true,
                },
            },
            messages: {
                phone: {
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
                            alert_float('success',data.message);
                            window.location.href = 'admin/membership_level/list';
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


        function changeEnd(_this, keySTT, name, keyStart, plus = false) {
            var startMoney = $(_this).val();
            if(plus) {
                startMoney++;
            }
            $(`input[data-key="${name}-${keySTT + 1}-${keyStart}"]`).val(startMoney);
        }
    </script>
@endsection
