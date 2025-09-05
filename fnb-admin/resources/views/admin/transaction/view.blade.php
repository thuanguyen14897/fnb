<style>
    .modal-content{
        background: #f8f9fa;
    }
    .modal-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
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

    .status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        color: white;
    }

    .day-tabs {
        display: flex;
        background: white;
        border-bottom: 1px solid #e0e0e0;
        overflow-x: auto;
        position: sticky;
        top: 0px;
        z-index: 9;
    }

    .day-tab {
        flex: none;
        text-align: center;
        padding: 15px 20px;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        white-space: nowrap;
        min-width: 100px;
        transition: all 0.3s ease;
    }

    .day-tab.active {
        border-bottom-color: #FF5A1F;
        background: #f8f9fa;
    }

    .day-tab-title {
        font-weight: 600;
        font-size: 14px;
        color: #333;
    }

    .day-tab-date {
        font-size: 12px;
        color: #666;
        margin-top: 2px;
    }
    .modal-content-new {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        max-height: 50vh; /* hoặc 70vh */
    }

    .day-section {
        margin-bottom: 30px;
    }

    .day-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .day-title {
        font-size: 18px;
        font-weight: 600;
        color: #FF5A1F;
    }

    .day-points {
        font-size: 14px;
        color: #666;
    }

    /* Timeline container for the entire day */
    .timeline-wrapper {
        position: relative;
    }

    /* Service item */
    .service-item {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        align-items: flex-start;
        position: relative;
        margin-left: 75px; /* Space for time */
    }

    .service-time {
        position: absolute;
        left: -90px;
        top: 15px;
        text-align: center;
        min-width: 50px;
    }

    .service-time-hour {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        line-height: 1.2;
    }

    .service-time-period {
        font-size: 12px;
        color: #666;
        line-height: 1;
    }

    /* Timeline line and dot */
    .timeline-dot {
        position: absolute;
        left: -40px;
        top: 20px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #FF5A1F;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        z-index: 2;
    }

    .timeline-line {
        position: absolute;
        left: -28px;
        top: 44px;
        width: 2px;
        background: #E0E0E0;
        height: calc(100% + 10px);
        z-index: 1;
    }

    /* Hide timeline line for last item */
    .service-item:last-child .timeline-line {
        display: none;
    }

    .service-info {
        flex: 1;
    }

    .service-name {
        font-weight: 600;
        font-size: 16px;
        color: #333;
        margin-bottom: 5px;
    }

    .service-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        /*margin-bottom: 8px;*/
    }

    .service-tag {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
    }

    .service-location {
        font-size: 12px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .service-details {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 12px;
        color: #666;
    }

    .service-detail {
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .service-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ddd;
        flex-shrink: 0;
        margin-right: 5px;
        margin-bottom: 5px;
    }
    .service-image {
        width: 100%;
        height: 100%;
        border-radius: 50%;
    }
    .service-name-wrap{
        display: flex;
        align-items: center;
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
                <div class="day-tabs">
                    @if(!empty($dtData['transaction_day']))
                        @foreach($dtData['transaction_day'] as $key => $value)
                            <div class="day-tab {{$key == 0 ? 'active' : ''}}" data-day="{{$value['id']}}">
                                <div class="day-tab-title">Ngày {{(++$key)}}</div>
                                <div class="day-tab-date">{{_dthuan($value['date'])}}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="modal-content-new">
                    @if(!empty($dtData['transaction_day']))
                        @foreach($dtData['transaction_day'] as $key => $value)
                            <div class="day-section" id="day-{{$value['id']}}">
                                <div class="day-header">
                                    <div class="day-title">Ngày {{(++$key)}}</div>
                                    <div class="day-points">{{count($value['transaction_day_item'])}} điểm đến</div>
                                </div>

                                <div class="timeline-wrapper">
                                    @if(!empty($value['transaction_day_item']))
                                        @foreach($value['transaction_day_item'] as $kk => $vv)
                                            @php
                                                $image_service = !empty($vv['service']['image_store']) ? $vv['service']['image_store'] : null;
                                                $dtImage = null;
                                                if (!empty($image_service)){
                                                    $dtImage = !empty($image_service[0]) ? $image_service[0]['image'] : null;
                                                }
                                            @endphp
                                            <div class="service-item">
                                                <div class="service-time">
                                                    <div class="service-time-hour">{{formatTimeAMPM($vv['hour'])['hour']}}</div>
                                                    <div class="service-time-period">{{formatTimeAMPM($vv['hour'])['type']}}</div>
                                                </div>
                                                <div class="timeline-dot">{{(++$kk)}}</div>
                                                <div class="timeline-line"></div>
                                                <div class="service-info">
                                                    <div class="service-name-wrap">
                                                        <div class="service-avatar">
                                                            <a href="{{$dtImage}}" data-lightbox="customer-profile">
                                                                <img src="{{$dtImage}}" alt="Hotel" class="service-image">
                                                            </a>
                                                        </div>
                                                        <div class="service-name">{{$vv['service']['name'] ?? ''}}</div>
                                                    </div>
                                                    <div class="service-meta">
                                                        <div class="service-tag" style="background: {{$vv['service']['group_category_service']['color']}};border: {{$vv['service']['group_category_service']['color_border']}}"> <img src="{{$vv['service']['category_service']['icon']}}" style="width: 20px"> {{$vv['service']['category_service']['name'] ?? ''}}</div>
                                                    </div>
                                                    <div class="service-details hide">
                                                        <div class="service-detail">🚗 0.7 km</div>
                                                        <div class="service-detail">⏱ 10 phút</div>
                                                    </div>
                                                </div>
                                                <div>
                                                    
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
    $(document).ready(function() {
        // Handle day tab clicks
        $('.day-tab').click(function() {
            const dayNumber = $(this).data('day');

            // Remove active class from all tabs
            $('.day-tab').removeClass('active');

            // Add active class to clicked tab
            $(this).addClass('active');

            // Scroll to the corresponding day section
            scrollToDay(dayNumber);
        });

        // Handle scroll events to update active tab
        $('.modal-content-new').scroll(function() {
            updateActiveTab();
        });
    });

    function scrollToDay(dayNumber) {
        const targetElement = document.getElementById('day-' + dayNumber);
        const modalContent = document.querySelector('.modal-content-new');

        if (targetElement && modalContent) {
            // Calculate the offset from the top of the modal content
            const elementRect = targetElement.getBoundingClientRect();
            const modalRect = modalContent.getBoundingClientRect();
            const currentScrollTop = modalContent.scrollTop;

            // Calculate target scroll position
            const targetScrollTop = currentScrollTop + elementRect.top - modalRect.top - 20;

            // Smooth scroll animation
            $(modalContent).animate({
                scrollTop: targetScrollTop
            }, 500);
        }
    }

    function updateActiveTab() {
        const modalContent = $('.modal-content-new');
        const scrollTop = modalContent.scrollTop();
        let activeDay = 1;

        // Find which section is currently in view
        $('.day-section').each(function(index) {
            const element = $(this)[0];
            const rect = element.getBoundingClientRect();
            const modalRect = modalContent[0].getBoundingClientRect();

            // Check if the section is in the visible area
            if (rect.top - modalRect.top <= 100) {
                activeDay = index + 1;
            }
        });

        // Update active tab
        $('.day-tab').removeClass('active');
        $('[data-day="' + activeDay + '"]').addClass('active');

        // Scroll tab into view if needed
        const activeTab = $('[data-day="' + activeDay + '"]');
        const tabsContainer = $('.day-tabs');
        if (activeTab.length && tabsContainer.length) {
            const tabPosition = activeTab.position();
            if (tabPosition) {
                const tabLeft = tabPosition.left;
                const tabWidth = activeTab.outerWidth();
                const containerWidth = tabsContainer.width();
                const currentScrollLeft = tabsContainer.scrollLeft();

                if (tabLeft < 0) {
                    tabsContainer.animate({
                        scrollLeft: currentScrollLeft + tabLeft - 20
                    }, 300);
                } else if (tabLeft + tabWidth > containerWidth) {
                    tabsContainer.animate({
                        scrollLeft: currentScrollLeft + (tabLeft + tabWidth - containerWidth) + 20
                    }, 300);
                }
            }
        }
    }
</script>
