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
            padding-left: 15px;
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
            font-size: 14px;
        }

        .criteria-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
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
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .example-table th {
            background: #6c757d;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 13px;
        }

        .example-table td {
            padding: 10px 12px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
            width: 150px;
        }

        .p-l-20 {
            padding-left: 40px;
        }

        .input-num:read-only {
            border: 0px !important;
        }
    </style>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="page-title text-capitalize">{{lang('c_membership_level')}}</h4>
            <ol class="breadcrumb">
                <li><a href="admin/dashboard">{{lang('dt_index')}}</a></li>
                <li><a href="admin/kpi/kpi_user">{{lang('c_membership_level')}}</a></li>
                <li class="active">{{lang('dt_list')}}</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card-box">
                <form action="admin/membership_level/updateMember" method="post" id="formMemberShip"
                      data-parsley-validate
                      novalidate
                      enctype="multipart/form-data">
                    {{csrf_field()}}
                    <div class="header">
                        <h3 class="text-center">{{$title}}</h3>
                    </div>
                    <div class="content">
                        <!-- Section 1: Các tiêu chí tính điểm -->
                        <div class="section">
                            <div class="section-title">I. CÁC TIÊU CHÍ TÍNH ĐIỂM</div>
                            <p style="margin-bottom: 20px; font-size: 14px; color: #666;">Để khuyến khích hoạt động chi
                                tiêu và gắn bó lâu dài, điểm thưởng được tính dựa trên 4 tiêu chí:</p>

                            <div class="subsection">
                                <div class="subsection-title">1.1. Chỉ tiêu trọng quý</div>
                                <div class="criteria-list">
                                    <div class="criteria-item">
                                        <div class="check-icon"></div>
                                        <span><strong>Ý nghĩa:</strong> Tăng thưởng theo tổng số tiền thanh toán trong quý.</span>
                                    </div>
                                    <div class="criteria-item">
                                        <div class="check-icon"></div>
                                        <span><strong>Quy tắc tính:</strong> Mức chi tiêu càng cao, điểm cộng nhiều.</span>
                                    </div>
                                    <div class="criteria-item">
                                        <div class="check-icon"></div>
                                        <span><strong>Khuyến khích thành toàn:</strong> Khách hàng được thưởng gia quý thưởng định kỳ.</span>
                                    </div>
                                </div>

                                <table class="reward-table">
                                    <thead>
                                    <tr>
                                        <th>Mức chi tiêu trong quý</th>
                                        <th>Điểm thưởng</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Từ 0 → dưới 1,000,000 VND</td>
                                        <td>0</td>
                                    </tr>
                                    <tr>
                                        <td>Từ 1,000,000 → dưới 3,000,000 VND</td>
                                        <td>20</td>
                                    </tr>
                                    <tr>
                                        <td>Từ 3,000,000 → dưới 9,000,000 VND</td>
                                        <td>45</td>
                                    </tr>
                                    <tr>
                                        <td>Từ 9,000,000 → dưới 15,000,000 VND</td>
                                        <td>80</td>
                                    </tr>
                                    <tr>
                                        <td>Từ 15,000,000 → dưới 30,000,000 VND</td>
                                        <td>151</td>
                                    </tr>
                                    <tr>
                                        <td>Từ 30,000,000 VND trở lên</td>
                                        <td>216</td>
                                    </tr>
                                    </tbody>
                                </table>

                                <div class="note-box">
                                    <strong>Ghi chú:</strong> Mức chi tiêu công dồn theo quý. Ví dụ: tháng 1 mua 5 triệu
                                    + tháng 2 mua 4 triệu + tháng 3 mua 7 triệu = 16 triệu → 151 điểm.
                                </div>
                            </div>

                            <div class="subsection">
                                <div class="subsection-title">1.2. Số lần mua hàng trong quý</div>
                                <div class="criteria-list">
                                    <div class="criteria-item">
                                        <div class="check-icon"></div>
                                        <span><strong>Ý nghĩa:</strong> Khuyến khích mua sắm đều đặn, không chỉ tập trung một lần.</span>
                                    </div>
                                </div>

                                <table class="reward-table">
                                    <thead>
                                    <tr>
                                        <th>Số lần mua hàng trong quý</th>
                                        <th>Điểm thưởng</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>1 → 2 lần</td>
                                        <td>6</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Section 2: Chi tiêu doanh số kinh doanh -->
                        <div class="section">
                            <div class="section-title">II. CHỈ TIÊU DOANH SỐ KINH DOANH</div>
                            <div class="subsection">
                                <div class="subsection-title">1. Năm tiêu chuẩn</div>
                                <p style="margin-bottom: 15px;"><strong>Doanh số mục tiêu:</strong> 4,8 tỷ đồng/tháng
                                </p>

                                <table class="example-table">
                                    <thead>
                                    <tr>
                                        <th>Quý</th>
                                        <th>Tỷ trọng</th>
                                        <th>Doanh số mục tiêu</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Tháng 1</td>
                                        <td>30%</td>
                                        <td>1.440 triệu đồng</td>
                                    </tr>
                                    <tr>
                                        <td>Tháng 2</td>
                                        <td>30%</td>
                                        <td>1.440 triệu đồng</td>
                                    </tr>
                                    <tr>
                                        <td>Tháng 3</td>
                                        <td>75%</td>
                                        <td>3.60 tỷ đồng</td>
                                    </tr>
                                    <tr>
                                        <td>Tháng 4</td>
                                        <td>100%</td>
                                        <td>4.8 tỷ đồng</td>
                                    </tr>
                                    </tbody>
                                </table>
                                <p style="font-style: italic; color: #666; text-align: center;"><em>Chỉ tiêu tháng =
                                        Doanh số quý ÷ 3</em></p>
                            </div>

                            <div class="subsection">
                                <div class="subsection-title">2. Tỷ trọng theo loại vải</div>
                                <div class="criteria-list">
                                    <p>• Cài thể tháng: 4.8 tỷ đồng</p>
                                    <p>• Cài thể quý: 14.4 tỷ đồng</p>
                                    <p>• Cài thể năm: 57.6 tỷ đồng</p>
                                </div>
                            </div>

                            <div class="subsection">
                                <div class="subsection-title">3. Thang kế hoạch thành</div>
                                <table class="example-table">
                                    <thead>
                                    <tr>
                                        <th>% Hoàn thành</th>
                                        <th>Điểm chuẩn hóa</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Dưới 50%</td>
                                        <td>20 điểm</td>
                                        <td>Không đạt</td>
                                    </tr>
                                    <tr>
                                        <td>50%-79%</td>
                                        <td>80 điểm</td>
                                        <td>Trung bình</td>
                                    </tr>
                                    <tr>
                                        <td>80%-95%</td>
                                        <td>90 điểm</td>
                                        <td>Tốt</td>
                                    </tr>
                                    <tr>
                                        <td>100%</td>
                                        <td>100 điểm</td>
                                        <td>Hoàn thành xuất sắc</td>
                                    </tr>
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
                                <tr>
                                    <td>Dưới 8</td>
                                    <td>50%</td>
                                    <td>50 điểm</td>
                                </tr>
                                <tr>
                                    <td>8-12</td>
                                    <td>80%</td>
                                    <td>80 điểm</td>
                                </tr>
                                <tr>
                                    <td>13-20</td>
                                    <td>90%</td>
                                    <td>90 điểm</td>
                                </tr>
                                <tr>
                                    <td>21-30</td>
                                    <td>100%</td>
                                    <td>100 điểm</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section 4: Tỷ lệ duy trì khách hàng -->
                        <div class="section">
                            <div class="section-title">IV. CHỈ TIÊU TỶ LỆ DUY TRÌ KHÁCH HÀNG THÀNH VIÊN</div>
                            <table class="reward-table">
                                <thead>
                                <tr>
                                    <th>Tỷ lệ duy trì (%)</th>
                                    <th>% Hoàn thành</th>
                                    <th>Điểm chuẩn hóa</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Dưới 50%</td>
                                    <td>50%</td>
                                    <td>50 điểm</td>
                                </tr>
                                <tr>
                                    <td>50-64%</td>
                                    <td>60%</td>
                                    <td>60 điểm</td>
                                </tr>
                                <tr>
                                    <td>65-79%</td>
                                    <td>80%</td>
                                    <td>80 điểm</td>
                                </tr>
                                <tr>
                                    <td>80-100%</td>
                                    <td>100%</td>
                                    <td>100 điểm</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section 5: Chỉ tiêu hành vi - chất lượng -->
                        <div class="section">
                            <div class="section-title">V. CÁC CHỈ TIÊU HÀNH VI - CHẤT LƯỢNG</div>
                            <table class="example-table">
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
                                <tr>
                                    <td>Doanh số kinh doanh</td>
                                    <td>50%</td>
                                </tr>
                                <tr>
                                    <td>Số hợp đồng mới</td>
                                    <td>15%</td>
                                </tr>
                                <tr>
                                    <td>Tỷ lệ duy trì KH thành viên</td>
                                    <td>25%</td>
                                </tr>
                                <tr>
                                    <td>Tinh thần - Kỷ luật</td>
                                    <td>10%</td>
                                </tr>
                                <tr style="background: #e8f4fd; font-weight: bold;">
                                    <td>Tổng cộng</td>
                                    <td>100%</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section 7: Cách tính điểm tổng kết -->
                        <div class="section">
                            <div class="section-title">VII. CÁCH TÍNH ĐIỂM TỔNG KẾT</div>
                            <div class="criteria-list">
                                <p><strong>1.</strong> Từ điểm chuẩn hóa theo loại kỹ quy điểm chuẩn hóa.</p>
                                <p><strong>2.</strong> Điểm tổng = Σ (Điểm chuẩn hóa × Trọng số) × 100 chỉ tiêu.</p>
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
                                <tr>
                                    <td>≥90</td>
                                    <td>Xuất sắc</td>
                                </tr>
                                <tr>
                                    <td>80-89</td>
                                    <td>Giỏi</td>
                                </tr>
                                <tr>
                                    <td>65-79</td>
                                    <td>Khá</td>
                                </tr>
                                <tr>
                                    <td>50-64</td>
                                    <td>Đạt yêu cầu</td>
                                </tr>
                                <tr>
                                    <td><50</td>
                                    <td>Không đạt - Cần cải thiện</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section 9: Ví dụ minh họa -->
                        <div class="section">
                            <div class="section-title">IX. VÍ DỤ MINH HỌA</div>
                            <div class="example-section">
                                <p style="text-align: center; font-weight: bold; margin-bottom: 20px;">Ví dụ tháng 04 -
                                    Tháng 6 - Năm 2</p>

                                <table class="example-table">
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
                        <div class="policy-grid">
                            <div class="policy-card">
                                <h4>X. CHÍNH SÁCH THƯỞNG - KỶ LUẬT</h4>
                                <table class="example-table">
                                    <thead>
                                    <tr>
                                        <th>Xếp hạng</th>
                                        <th>Chính sách thưởng</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Xuất sắc</td>
                                        <td style="text-align: left; font-size: 11px;">Thưởng thêm 1% doanh thu hoạch
                                            thành lần thưởng tiền thành
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Giỏi</td>
                                        <td style="text-align: left; font-size: 11px;">Thưởng mức trung bình (% doanh
                                            thu tiền thành hỗn hoạc khác thế thánh)
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="policy-card">
                                <h4>XI. TỶ LỆ CHỈ TRẢ THEO XẾP HẠNG KPI</h4>
                                <table class="example-table">
                                    <thead>
                                    <tr>
                                        <th>Xếp hạng</th>
                                        <th>Tỷ lệ chi trả hoa hồng lương tác ra</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Xuất sắc</td>
                                        <td>100%</td>
                                    </tr>
                                    <tr>
                                        <td>Giỏi</td>
                                        <td>90%</td>
                                    </tr>
                                    <tr>
                                        <td>Khá</td>
                                        <td>75%</td>
                                    </tr>
                                    <tr>
                                        <td>Đạt yêu cầu</td>
                                        <td>60%</td>
                                    </tr>
                                    <tr>
                                        <td>Không đạt (<50%)</td>
                                        <td>50%</td>
                                    </tr>
                                    </tbody>
                                </table>
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
                            alert_float('success', data.message);
                            window.location.href = 'admin/membership_level/list';
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


        function changeEnd(_this, keySTT, name, keyStart, plus = false) {
            var startMoney = $(_this).val();
            if (plus) {
                startMoney++;
            }
            $(`input[data-key="${name}-${keySTT + 1}-${keyStart}"]`).val(startMoney);
        }
    </script>
@endsection
