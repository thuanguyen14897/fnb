@extends('admin.layouts.index')
@section('content')
    <style>
        .header {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 3px solid #ff6b35;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .content {
            padding: 30px;
        }

        .section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            border-left: 4px solid #ff6b35;
            background: #f8f9fa;
            padding: 15px 15px 15px 20px;
            border-radius: 5px;
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

        .reward-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .reward-table thead th {
            background: linear-gradient(135deg, #ff6b35 0%, #f55a2c 100%);
            color: white;
            font-weight: bold;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reward-table tbody td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 15px;
        }

        .reward-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .reward-table tbody tr:hover {
            background-color: #e8f4fd;
        }

        .note-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 1px solid #90caf9;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-size: 13px;
            color: #1565c0;
            font-style: italic;
        }

        .example-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }

        .example-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .example-table th {
            background: #6c757d;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 15px;
        }

        .example-table td {
            padding: 10px 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 15px;
        }

        .table-target td:nth-child(1) {
            width: 80px;
        }

        .highlight-result {
            background: #d4edda;
            color: #155724;
            font-weight: bold;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            margin-top: 15px;
        }

        .policy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .policy-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .policy-card h4 {
            color: #ff6b35;
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .warning-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .warning-section h4 {
            color: #856404;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .warning-section ul {
            margin-left: 20px;
        }

        .warning-section li {
            margin-bottom: 8px;
            color: #856404;
        }
    </style>
    <style>
        .input-num {
            border-top: 0px;
            border-left: 0px;
            border-right: 0px;
            text-align: center;
            padding: 5px;
            width: 120px;
        }

        .p-l-20 {
            padding-left: 40px;
        }

        .input-num:read-only {
            border: 0px !important;
        }

        .example-box > p {
            font-size: 15px;
        }

        .example-box > ul > li {
            font-size: 15px;
        }

        .criteria-item > span {
            width: 90%;
        }
        .not-background{
            background: unset;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('KPI nhân viên')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/kpi/kpi_user">{{lang('KPI nhân viên')}}</a></li>
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
                        <form action="admin/kpi/submitKPI" method="post" id="formKpi"
                              data-parsley-validate
                              novalidate
                              enctype="multipart/form-data">
                            {{csrf_field()}}
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <div class="">
                                        <h3 class="text-center">{{$title}}</h3>
                                    </div>
                                    <div class="content">
                                        <!-- Section 1: Các tiêu chí tính điểm -->
                                        <div class="section">
                                            <div class="section-title">I. Mục tiêu</div>
                                            <div class="subsection">
                                                <div class="criteria-list">
                                                    <div class="criteria-item">
                                                        <div class="check-icon"></div>
                                                        <span>Đảm bảo công bằng, minh bạch trong đánh giá kết quả công việc.</span>
                                                    </div>
                                                    <div class="criteria-item">
                                                        <div class="check-icon"></div>
                                                        <span>Tạo động lực phấn đấu và phát triển năng lực</span>
                                                    </div>
                                                    <div class="criteria-item">
                                                        <div class="check-icon"></div>
                                                        <span>Là căn cứ xét thưởng, kỷ luật và điều chỉnh vị trí công tác</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="section">
                                            <div class="section-title">II. CHỈ TIÊU DOANH SỐ KINH DOANH</div>
                                            <div class="subsection">
                                                <div class="subsection-title">1. Năm tiêu chuẩn</div>
                                                <p style="margin-bottom: 15px;"><strong>Doanh số mục tiêu:</strong>
                                                    <input
                                                        name="target_month" type="text" class="input-num target_month"
                                                        onchange="formatNumBerKeyChange(this);totalTarget()"
                                                        value="{{formatMoney(($setting_kpi->target_month ?? 0))}}"/>
                                                    VNĐ/tháng
                                                </p>

                                                <table class="reward-table table-target">
                                                    <thead>
                                                    <tr>
                                                        <th class="hide">
                                                            <button type="button"
                                                                    class="btn btn-xs btn-default btn-icon"
                                                                    onclick="addRow()"><i class="fa fa-plus"
                                                                                          aria-hidden="true"></i>
                                                            </button>
                                                        </th>
                                                        <th>Tỷ trọng (%)</th>
                                                        <th>Doanh số mục tiêu</th>
                                                        <th class="hide"></th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        $counter = 0;
                                                    @endphp
                                                    @if(!empty($dtNumberRatioKpi))
                                                        @foreach($dtNumberRatioKpi as $key => $value)
                                                            <tr>
                                                                <td class="hide">
                                                                    <div class="text-center stt">{{(++$key)}}</div>
                                                                    <input type="hidden" name="counter[]"
                                                                           value="{{$counter}}">
                                                                    <input type="hidden" name="id[]"
                                                                           value="{{$value->id}}">
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="ratio[{{$counter}}]"
                                                                           min="0" max="100"
                                                                           value="{{$value->ratio}}" required
                                                                           onchange="formatNumBerKeyChange(this);totalTarget()"
                                                                           class="input-num ratio">
                                                                </td>
                                                                <td>
                                                                    <div><span
                                                                            class="target_money">{{formatMoney($value->money)}}</span><span> VNĐ</span>
                                                                    </div>
                                                                </td>
                                                                <td class="hide">
                                                                    <div class="text-center"><i
                                                                            class="fa fa-remove btn btn-danger remove-row"></i>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $counter ++;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                    </tbody>
                                                </table>
                                                <p style="font-style: italic; color: #666; text-align: center;"><em>Chỉ
                                                        tiêu tháng =
                                                        Doanh số quý ÷ 3</em></p>
                                            </div>

                                            <div class="subsection">
                                                <div class="subsection-title">2. Từ năm thứ 2 trở đi</div>
                                                <div class="criteria-list">
                                                    <p>• Chỉ tiêu tháng: <input
                                                            name="target_month_two" type="text" class="input-num"
                                                            onchange="formatNumBerKeyChange(this)"
                                                            value="{{formatMoney(($setting_kpi->target_month_two ?? 0))}}"/>
                                                        VNĐ</p>
                                                    <p>• Chỉ tiêu quý: <span
                                                            class="target_precious">{{ formatMoney(($setting_kpi->target_month_two ?? 0) * 3) }}</span>
                                                        VNĐ</p>
                                                    <p>• Chỉ tiêu năm: <span
                                                            class="target_year">{{ formatMoney(($setting_kpi->target_month_two ?? 0) * 12) }}</span>
                                                        VNĐ</p>
                                                </div>
                                            </div>

                                            <div class="subsection">
                                                <div class="subsection-title">3. Tháng % hoàn thành</div>
                                                <table class="reward-table">
                                                    <thead>
                                                    <tr>
                                                        <th>% Hoàn thành</th>
                                                        <th>Điểm chuẩn hóa</th>
                                                        <th>Ghi chú</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        $counterPercent = 0;
                                                    @endphp
                                                    @if(!empty($dtRatioPercentKpi))
                                                        @foreach($dtRatioPercentKpi as $key => $value)
                                                            <tr>
                                                                <td>
                                                                    @if($value->id == 4)
                                                                        <input type="number"
                                                                               name="percent_start[{{$counterPercent}}]"
                                                                               min="0" max="100"
                                                                               value="{{$value->percent_start}}"
                                                                               onchange="formatNumBerKeyChange(this);"
                                                                               class="input-num percent_start">
                                                                        <input type="hidden"
                                                                               name="percent_end[{{$counterPercent}}]"
                                                                               value="{{$value->percent_end}}"
                                                                               onchange="formatNumBerKeyChange(this);"
                                                                               class="input-num percent_end">
                                                                        %
                                                                    @else
                                                                        Từ
                                                                        <input type="number"
                                                                               name="percent_start[{{$counterPercent}}]"
                                                                               min="0" max="100"
                                                                               value="{{$value->percent_start}}"
                                                                               onchange="formatNumBerKeyChange(this);"
                                                                               class="input-num percent_start">
                                                                        Đến <
                                                                        <input type="number"
                                                                               name="percent_end[{{$counterPercent}}]"
                                                                               min="0" max="100"
                                                                               value="{{$value->percent_end}}"
                                                                               onchange="formatNumBerKeyChange(this);"
                                                                               class="input-num percent_end">
                                                                        %
                                                                    @endif
                                                                    <input type="hidden" name="counterPercent[]"
                                                                           value="{{$counterPercent}}">
                                                                    <input type="hidden"
                                                                           name="ratio_percent_id[{{$counterPercent}}]"
                                                                           value="{{$value->id}}">
                                                                </td>
                                                                <td>
                                                                    <input type="number"
                                                                           name="point[{{$counterPercent}}]" min="0"
                                                                           max="100" value="{{$value->point}}" required
                                                                           onchange="formatNumBerKeyChange(this);"
                                                                           class="input-num point"> Điểm
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="note[{{$counterPercent}}]"
                                                                           value="{{$value->note}}"
                                                                           class="form-control">
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $counterPercent ++;
                                                            @endphp
                                                        @endforeach
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Section 3: Chỉ tiêu số hợp đồng mới -->
                                        <div class="section">
                                            <div class="section-title">III. CHỈ TIÊU SỐ HỢP ĐỒNG MỚI</div>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Số hợp đồng mới/tháng</th>
                                                    <th>% Hoàn thành</th>
                                                    <th>Điểm chuẩn hóa</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $counterContract = 0;
                                                @endphp
                                                @if(!empty($dtTargetContractKpi))
                                                    @foreach($dtTargetContractKpi as $key => $value)
                                                        <tr>
                                                            <td>

                                                                Từ
                                                                <input type="number"
                                                                       name="contract_number_start[{{$counterContract}}]"
                                                                       min="0" max="100"
                                                                       value="{{$value->contract_number_start}}"
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num contract_number_start">
                                                                Đến <
                                                                <input type="number"
                                                                       name="contract_number_end[{{$counterContract}}]"
                                                                       min="0" max="3000"
                                                                       value="{{$value->contract_number_end}}"
                                                                       class="input-num contract_number_end">
                                                                <input type="hidden" name="counterContract[]"
                                                                       value="{{$counterContract}}">
                                                                <input type="hidden"
                                                                       name="target_contract_id[{{$counterContract}}]"
                                                                       value="{{$value->id}}">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="percent[{{$counterContract}}]" min="0"
                                                                       max="100" value="{{$value->percent}}" required
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num"> %
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="point_contract[{{$counterContract}}]"
                                                                       min="0"
                                                                       max="100" value="{{$value->point}}" required
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num point_contract"> Điểm
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $counterContract ++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Section 4: Tỷ lệ duy trì khách hàng -->
                                        <div class="section">
                                            <div class="section-title">IV. CHỈ TIÊU TỶ LỆ DUY TRÌ KHÁCH HÀNG THÀNH
                                                VIÊN
                                            </div>
                                            <p style="margin-bottom: 15px;"><strong>Mục tiêu thành viên:</strong> <input
                                                    name="target_member_month" type="text"
                                                    class="input-num target_member_month"
                                                    onchange="formatNumBerKeyChange(this);totalTargetMember()"
                                                    value="{{formatMoney(($setting_kpi->target_member_month ?? 0))}}"/>
                                                SL/tháng
                                            </p>
                                            <table class="reward-table table-target-member">
                                                <thead>
                                                <tr>
                                                    <th>Tỷ trọng (%)</th>
                                                    <th>Mục tiêu thành viên</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $counterMemberRatio = 0;
                                                @endphp
                                                @if(!empty($dtMemberNumberRatioKpi))
                                                    @foreach($dtMemberNumberRatioKpi as $key => $value)
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="counterMemberRatio[]"
                                                                       value="{{$counterMemberRatio}}">
                                                                <input type="hidden" name="member_number_ratio_kpi_id[]"
                                                                       value="{{$value->id}}">
                                                                <input type="number"
                                                                       name="member_ratio[{{$counterMemberRatio}}]"
                                                                       min="0" max="100"
                                                                       value="{{$value->ratio}}" required
                                                                       onchange="formatNumBerKeyChange(this);totalTargetMember()"
                                                                       class="input-num member_ratio">
                                                            </td>
                                                            <td>
                                                                <div><span
                                                                        class="target_member">{{formatMoney($value->member)}}</span><span> SL</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $counterMemberRatio ++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Tỷ lệ duy trì (%)</th>
                                                    <th>% Hoàn thành</th>
                                                    <th>Điểm chuẩn hóa</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $counterRetentionRate = 0;
                                                @endphp
                                                @if(!empty($dtRetentionRateCustomerKpi))
                                                    @foreach($dtRetentionRateCustomerKpi as $key => $value)
                                                        <tr>
                                                            <td>

                                                                Từ
                                                                <input type="number"
                                                                       name="point_start[{{$counterRetentionRate}}]"
                                                                       min="0" max="100" value="{{$value->point_start}}"
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num point_start">
                                                                Đến {{$value->id == 4 ? '<=' : '<' }}
                                                                <input type="number"
                                                                       name="point_end[{{$counterRetentionRate}}]"
                                                                       min="0"
                                                                       max="100" value="{{$value->point_end}}"
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num point_end">
                                                                <input type="hidden" name="counterRetentionRate[]"
                                                                       value="{{$counterRetentionRate}}">
                                                                <input type="hidden"
                                                                       name="retention_rate_customer_id[{{$counterRetentionRate}}]"
                                                                       value="{{$value->id}}">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="percent_retention_rate[{{$counterRetentionRate}}]"
                                                                       min="0"
                                                                       max="100" value="{{$value->percent}}" required
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num"> %
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="point_retention_rate[{{$counterRetentionRate}}]"
                                                                       min="0"
                                                                       max="100" value="{{$value->point}}" required
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num point_retention_rate"> Điểm
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $counterRetentionRate ++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Section 5: Chỉ tiêu hành vi - chất lượng -->
                                        <div class="section">
                                            <div class="section-title">V. CÁC CHỈ TIÊU HÀNH VI - CHẤT LƯỢNG</div>
                                            <div class="subsection">
                                                <div class="criteria-list">
                                                    <div class="criteria-item">
                                                        <div class="check-icon"></div>
                                                        <span>Chỉ cần vi phạm 1 lần cũng sẽ mất hết trọng số theo quy định.</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Chỉ tiêu</th>
                                                    <th>Trọng số (%)</th>
                                                    <th>Cách đánh giá</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>Tinh thần - Kỷ luật</td>
                                                    <td>10%</td>
                                                    <td>Mỗi lỗi vi phạm bị trừ điểm</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Section 6: Trọng số các nhóm chỉ tiêu -->
                                        <div class="section">
                                            <div class="section-title">VI. TRỌNG SỐ CÁC NHÓM CHỈ TIÊU</div>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Nhóm chỉ tiêu</th>
                                                    <th>Trọng số (%)</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php
                                                    $counterWeightTagert = 0;
                                                    $totalWeightTagert = 0;
                                                @endphp
                                                @if(!empty($dtWeightTagertKpi))
                                                    @foreach($dtWeightTagertKpi as $key => $value)
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                       name="name_tagert[{{$counterWeightTagert}}]"
                                                                       value="{{$value->name_tagert}}"
                                                                       class="form-control name_tagert">
                                                                <input type="hidden" name="counterWeightTagert[]"
                                                                       value="{{$counterWeightTagert}}">
                                                                <input type="hidden"
                                                                       name="weight_tagert_kpi_id[{{$counterWeightTagert}}]"
                                                                       value="{{$value->id}}">
                                                                <input type="hidden"
                                                                       name="type_tagert_kpi[{{$counterWeightTagert}}]"
                                                                       value="{{$value->type}}">
                                                            </td>
                                                            <td>
                                                                <input type="number"
                                                                       name="weight[{{$counterWeightTagert}}]" min="0"
                                                                       max="100" value="{{$value->weight}}" required
                                                                       onchange="formatNumBerKeyChange(this);"
                                                                       class="input-num"> %
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $totalWeightTagert += $value->weight;
                                                            $counterWeightTagert ++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                                <td style="text-align: left !important; font-weight: bold">Tổng cộng
                                                </td>
                                                <td class="text-center" style="font-weight: bold">{{$totalWeightTagert}}
                                                    %
                                                </td>
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Section 7: Cách tính điểm tổng kết -->
                                        <div class="section">
                                            <div class="section-title">VII. CÁCH TÍNH ĐIỂM TỔNG KẾT</div>
                                            <div class="criteria-list">
                                                <p><strong>1.</strong> Từ điểm chuẩn hóa theo loại kỹ quy điểm chuẩn
                                                    hóa.</p>
                                                <p><strong>2.</strong> Điểm tổng = Σ (Điểm chuẩn hóa × Trọng số) × 100
                                                    chỉ tiêu.</p>
                                                <p><strong>3.</strong> Công thức điểm chính phần = Tổng điểm KPI.</p>
                                            </div>
                                        </div>

                                        <!-- Section 8: Bảng xếp hạng -->
                                        <div class="section">
                                            <div class="section-title">VIII. BẢNG XẾP HẠNG CUỐI CÙNG</div>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Tổng điểm</th>
                                                    <th>Xếp hạng</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(!empty($dtRatingKpi))
                                                    @foreach($dtRatingKpi as $key => $value)
                                                        @php
                                                            $counterRatingKpi = $value->id;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                @if($value->id == 1)
                                                                    >=
                                                                    <input type="number"
                                                                           name="point_start_kpi[{{$counterRatingKpi}}]"
                                                                           min="0" max="100"
                                                                           value="{{$value->point_start_kpi}}"
                                                                           onchange="formatNumBerKeyChange(this);"
                                                                           class="input-num point_start_kpi">
                                                                    <input type="hidden"
                                                                           name="point_end_kpi[{{$counterRatingKpi}}]"
                                                                           value="{{$value->point_end_kpi}}"
                                                                           onchange="formatNumBerKeyChange(this);"
                                                                           class="input-num point_end_kpi">
                                                                @else
                                                                    Từ
                                                                    <input type="number"
                                                                           name="point_start_kpi[{{$counterRatingKpi}}]"
                                                                           min="0" max="100"
                                                                           value="{{$value->point_start_kpi}}"
                                                                           onchange="formatNumBerKeyChange(this);"
                                                                           class="input-num point_start_kpi">
                                                                    Đến <
                                                                    <input type="number"
                                                                           name="point_end_kpi[{{$counterRatingKpi}}]"
                                                                           min="0" max="100"
                                                                           value="{{$value->point_end_kpi}}"
                                                                           onchange="formatNumBerKeyChange(this);"
                                                                           class="input-num point_end_kpi">
                                                                @endif
                                                                <input type="hidden" name="counterRatingKpi[]"
                                                                       value="{{$counterRatingKpi}}">
                                                                <input type="hidden"
                                                                       name="rating_kpi_id[{{$counterRatingKpi}}]"
                                                                       value="{{$value->id}}">
                                                            </td>
                                                            <td>
                                                                {{$value->name}}
                                                                <input type="hidden"
                                                                       name="name_rating[{{$counterRatingKpi}}]"
                                                                       value="{{$value->name}}"
                                                                       class="form-control name_rating">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <!-- Section 9: Ví dụ minh họa -->
                                        <div class="section">
                                            <div class="section-title">IX. VÍ DỤ MINH HỌA</div>
                                            <div class="example-section">
                                                <p style="text-align: center; font-weight: bold; margin-bottom: 20px;">
                                                    Ví dụ tháng 04 -
                                                    Tháng 6 - Năm 2</p>

                                                <table class="reward-table">
                                                    <thead>
                                                    <tr>
                                                        <th>Chỉ tiêu</th>
                                                        <th>Kết quả</th>
                                                        <th>% Hoàn thành</th>
                                                        <th>Điểm chuẩn hóa</th>
                                                        <th>Điểm thành phần</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <tr>
                                                        <td>Doanh số</td>
                                                        <td>3.6 tỷ</td>
                                                        <td>75%</td>
                                                        <td>80</td>
                                                        <td>40 điểm</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Số hợp đồng mới</td>
                                                        <td>15</td>
                                                        <td>90%</td>
                                                        <td>90</td>
                                                        <td>13.5 điểm</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Duy trì KH thành viên</td>
                                                        <td>85%</td>
                                                        <td>100%</td>
                                                        <td>100</td>
                                                        <td>25 điểm</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Tinh thần - kỷ luật</td>
                                                        <td>Không vi phạm</td>
                                                        <td>100</td>
                                                        <td>100</td>
                                                        <td>10 điểm</td>
                                                    </tr>
                                                    </tbody>
                                                </table>

                                                <div class="highlight-result">
                                                    Tổng điểm: 40 + 13.5 + 25 + 10 = 88.5 điểm<br>
                                                    <strong>Xếp loại: Giỏi</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 10 & 11: Chính sách thưởng và tỷ lệ chi trả -->
                                        <div class="section">
                                            <div class="section-title">X. CHÍNH SÁCH THƯỞNG - KỶ LUẬT</div>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Xếp hạng</th>
                                                    <th>Chính sách thưởng</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>Xuất sắc</td>
                                                    <td style="text-align: left;">Thưởng thêm 1% doanh thu hoạch
                                                        thành lần thưởng tiền thành
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Giỏi</td>
                                                    <td style="text-align: left;">Thưởng mức trung bình (% doanh
                                                        thu tiền thành hỗn hoạc khác thế thánh)
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="section">
                                            <div class="section-title">XI. TỶ LỆ CHỈ TRẢ THEO XẾP HẠNG KPI</div>
                                            <table class="reward-table">
                                                <thead>
                                                <tr>
                                                    <th>Xếp hạng</th>
                                                    <th>Tỷ lệ chi trả lợi nhuận tạo ra (%)</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if(!empty($dtRatingKpi))
                                                    @foreach($dtRatingKpi as $key => $value)
                                                        @php
                                                            $counterRatingKpi = $value->id;
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                {{$value->name}}
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                       name="percent_profit[{{$counterRatingKpi}}]"
                                                                       min="0" max="100"
                                                                       value="{{$value->percent_profit}}"
                                                                       class="input-num percent_profit"> %
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="section">
                                            <div class="section-title">XII. KỶ LUẬT – HUẤN LUYỆN</div>
                                            <div class="p-l-20">
                                                <div class="example-box">
                                                    <p><strong>Không đạt 3 tháng liên tiếp:</strong></p>
                                                    <ul>
                                                        <li>Ban hành <strong>Kế hoạch cải thiện cá nhân</strong></li>
                                                        <li>Nhắc nhở chính thức bằng văn bản</li>
                                                        <li>Cân nhắc điều chuyển vị trí</li>
                                                    </ul>
                                                    <p><strong>Không đạt kế hoạch 6 tháng lũy kế:</strong></p>
                                                    <ul>
                                                        <li>Xem xét <strong>chấm dứt hợp đồng</strong></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr/>
                                    <div class="form-group text-right m-b-0">
                                        <button class="btn btn-primary waves-effect waves-light" type="submit">
                                            {{lang('dt_save')}}
                                        </button>
                                    </div>
                                </div>
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
                                    <span>Công thức tính sẽ được tính bằng cách tạo mới trực tiếp trên phầm mềm vào mỗi cuối tháng (KPI -> Thống kê KPI nhân viên -> tạo mới)</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Trên mỗi nhân viên có check để biết nhân viên đó là NVKD (Nhân viên -> NVKD KPI)</span>
                                </div>
                                <div class="criteria-item">
                                    <div class="check-icon"></div>
                                    <span>Chủ động theo dõi được KPI hiện tại của nhân viên theo những tiêu chí</span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="section-title not-background">II. CHỈ TIÊU DOANH SỐ KINH DOANH</div>

                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Điều kiện lấy sẽ như sau. Trên mỗi đối tác có gắn nhân viên phụ trách dựa vào đó sẽ lấy tổng thu nhập trên tất cả các phiếu thu đã thanh toán của đối tác mà nhân viên đó phụ trách trên từng tháng (Giao dịch -> Phiếu thu)</span>
                                        </div>
                                    </div>


                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Dựa vào số lượng mục tiêu -> % hoàn thành -> Điểm chuẩn hóa</span>
                                        </div>
                                    </div>

                                    <div class="subsection-title">1. Năm tiêu chuẩn</div>
                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Dựa vào bảng này để biết mức % hoàn thành dựa trên doanh thu đã thống kê</span>
                                        </div>
                                    </div>

                                    <div class="subsection-title">3. Tháng % hoàn thành</div>
                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Dựa vào bảng này để biết số điểm chuẩn hóa đạt được dựa trên % hoàn thành ở trên</span>
                                        </div>
                                    </div>

                                    <div class="section-title not-background">III. CHỈ TIÊU SỐ HỢP ĐỒNG MỚI</div>
                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Điều kiện lấy sẽ như sau. Trên mỗi đối tác có gắn nhân viên phụ trách dựa vào đó sẽ lấy những gian hàng nào đăng ký mới ( đã duyệt) của đối tác mình quản lý trên từng tháng (Đối tác -> Danh sách gian hàng)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="section-title not-background">IV. CHỈ TIÊU TỶ LỆ DUY TRÌ KHÁCH HÀNG THÀNH VIÊN</div>

                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Điều kiện lấy sẽ như sau. Trên mỗi đối tác, thành viên có gắn nhân viên phụ trách dựa vào đó sẽ thống kê ra những thành viên, đối tác nào còn hạn sử dụng phần mềm để thống kê trên từng tháng (Dựa vào ngày hết hạn sử dụng ngay thời điểm tạo tính KPI)</span>
                                        </div>
                                    </div>

                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Những thành viên thuộc giới thiệu của đối tác thì lấy nhân viên phụ trách của đối tác gắn vào thành viên đó luôn</span>
                                        </div>
                                    </div>

                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Dựa vào số lượng mục tiêu -> % hoàn thành -> Điểm chuẩn hóa</span>
                                        </div>
                                    </div>

                                    <div class="section-title not-background">V. CÁC CHỈ TIÊU HÀNH VI - CHẤT LƯỢNG</div>
                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Điều kiện lấy sẽ như sau. Dựa vào phiếu vi phạm được lập trên phầm mềm của mỗi nhân viên (KPI -> Phiếu vi pham)</span>
                                        </div>
                                    </div>

                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Chỉ cần vi phạm 1 lần là mất hết điểm ở phần này</span>
                                        </div>
                                    </div>

                                    <div class="section-title not-background">VI. TRỌNG SỐ CÁC NHÓM CHỈ TIÊU</div>
                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>Dựa vào trọng số của mỗi mục tiêu cùng với số điểm chuẩn hóa của mỗi mục tiêu để ra được điểm tính KPI (điểm chuẩn hóa x % trọng số của chỉ tiêu)</span>
                                        </div>
                                    </div>

                                    <div class="section-title not-background">VIII. BẢNG XẾP HẠNG CUỐI CÙNG</div>
                                    <div class="criteria-list">
                                        <div class="criteria-item">
                                            <div class="check-icon"></div>
                                            <span>So sánh số điểm KPI để tính ra được xếp hạng KPI</span>
                                        </div>
                                    </div>
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
        $("#formKpi").validate({
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
                            window.location.href = 'admin/kpi/kpi_user';
                        } else {
                            alert_float('error', data.message);
                        }
                    })
                    .fail(function (err) {
                        htmlError = '';
                        for (var [el, message] of Object.entries(err.responseJSON.errors)) {
                            htmlError += `<div>${message}</div>`;
                        }
                        alert_float('error', htmlError);
                    });
                return false;
            }
        });

        var counter = {{$counter}};

        function addRow() {
            var tr = $('<tr></tr>');
            var td_delete = $('<td class="text-center"></td>');
            var td_stt = $('<td></td>');
            var td_ratio = $(`<td></td>`);
            var td_money = $('<td></td>');
            td_delete.append('<div class="text-center"><i class="fa fa-remove btn btn-danger remove-row"></i></div>');
            td_stt.append('<div class="text-center stt"></div><input type="hidden" name="counter[]" value="' + counter + '">');
            td_ratio.append('<input type="number" name="ratio[' + counter + ']" min="0" max="100" value="" required onchange="formatNumBerKeyChange(this);totalTarget()" class="input-num ratio">');
            td_money.append(`<div><span class="target_money"></span><span> VNĐ</span></div>`);


            tr.append(td_stt);
            tr.append(td_ratio);
            tr.append(td_money);
            tr.append(td_delete);
            $('.table-target tbody').append(tr);
            counter++;
            totalTarget();
        }

        $(document).on('click', '.remove-row', function (event) {
            event.preventDefault();
            tr = $(this).closest('tr');
            tr.remove();
            totalTarget();
        })

        function totalTarget() {
            tb = '.table-target tbody tr';
            var n = $(tb).length;
            var stt = 0;
            target_month = intVal($(".target_month").val());
            for (ii = 0; ii < n; ii++) {
                stt++;
                element = $(tb)[ii];
                $(element).find('.stt').html(stt);
                ratio = intVal($(element).find('.ratio').val());
                target_money = target_month * ratio / 100;
                $(element).find('.target_money').html(formatMoney(target_money));
            }
        }

        function totalTargetMember() {
            tb = '.table-target-member tbody tr';
            var n = $(tb).length;
            var stt = 0;
            target_member_month = intVal($(".target_member_month").val());
            for (ii = 0; ii < n; ii++) {
                stt++;
                element = $(tb)[ii];
                $(element).find('.stt').html(stt);
                member_ratio = intVal($(element).find('.member_ratio').val());
                target_member = target_member_month * member_ratio / 100;
                $(element).find('.target_member').html(formatMoney(target_member));
            }
        }

        @if(empty($dtNumberRatioKpi))
        addRow();
        @endif
    </script>
@endsection
