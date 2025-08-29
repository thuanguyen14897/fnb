<style>
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(5px);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modal-overlay.show {
        opacity: 1;
    }

    .modal-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.7);
        width: 90%;
        max-width: 900px;
        max-height: 90vh;
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        transition: transform 0.3s ease;
        overflow: hidden;
    }

    .modal-overlay.show .modal-container {
        transform: translate(-50%, -50%) scale(1);
    }

    .modal-close:hover {
        background: rgba(255,255,255,0.2);
    }

    .modal-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9ff;
        border-radius: 10px;
    }

    .info-item {
        text-align: center;
    }

    .info-label {
        font-size: 15px;
        color: #718096;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #2d3748;
    }

    .modal-days {
        display: grid;
        gap: 20px;
    }

    .modal-day {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .modal-day:hover {
        border-color: #ff912c;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
    }

    .modal-day-header {
        background: linear-gradient(135deg, #ff912c, #FF5A1F);
        color: white;
        padding: 15px 20px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-services {
        padding: 20px;
    }

    .modal-service {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #f7fafc;
        border-radius: 8px;
        margin-bottom: 10px;
        border-left: 4px solid #ff912c;
    }

    .service-name {
        font-weight: 500;
        color: #2d3748;
    }

    .service-price {
        font-weight: 600;
    }

    .modal-total {
        margin-top: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #38b2ac, #4299e1);
        color: white;
        border-radius: 10px;
        text-align: center;
    }

    .status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }
    .service-details{
        flex: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .service-image {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        object-fit: cover;
        margin-right: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
</style>
<div class="modal-dialog transaction-modal" style="width: 50%;">
    <div class="modal-content" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$dtData['reference_no']}} - {{$dtData['name']}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="modal-info">
                    <div class="info-item">
                        <div class="info-label">Thành viên</div>
                        <div class="info-value">👤 {{!empty($dtData['customer']) ? $dtData['customer']['fullname'] : ''}}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Trạng thái</div>
                        <div class="info-value"><span class="status" style="background: {{$dtData['status']['color']}}">{{$dtData['status']['name']}}</span></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ngày tạo</div>
                        <div class="info-value">📅 {{_dt($dtData['date'])}}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Số ngày</div>
                        <div class="info-value">🗓️ {{$dtData['day']['day']}}</div>
                    </div>
                </div>
                <div class="modal-days">
                    @if(!empty($dtData['transaction_day']))
                        @foreach($dtData['transaction_day'] as $key => $value)
                            <div class="modal-day">
                                <div class="modal-day-header">
                                    <span>📅 Ngày {{(++$key)}}: {{_dt_new($value['date'],false)}}</span>
                                </div>
                                <div class="modal-services">
                                    @if(!empty($value['transaction_day_item']))
                                        @foreach($value['transaction_day_item'] as $kk => $vv)
                                            @php
                                                $image_service = !empty($vv['service']['image_store']) ? $vv['service']['image_store'] : null;
                                                $dtImage = null;
                                                if (!empty($image_service)){
                                                    $dtImage = !empty($image_service[0]) ? $image_service[0]['image'] : null;
                                                }
                                            @endphp
                                            <div class="modal-service">
                                                <a href="{{$dtImage}}" data-lightbox="customer-profile">
                                                    <img src="{{$dtImage}}" alt="Hotel" class="service-image">
                                                </a>
                                                <div class="service-details">
                                                    <div class="service-info">
                                                        <div class="service-name">{{$vv['hour']}}</div>
                                                        <div class="service-description">{{$vv['service']['name']}}</div>
                                                    </div>
                                                    <span class="service-price">
                                                        <img src="{{$vv['service']['category_service']['icon']}}" style="width: 20px"> {{$vv['service']['category_service']['name']}}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
    </div>
</div>
<script>
</script>
