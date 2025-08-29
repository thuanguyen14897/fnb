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
        border-color: #667eea;
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
        color: #38b2ac;
    }

    .modal-total {
        margin-top: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #38b2ac, #4299e1);
        color: white;
        border-radius: 10px;
        text-align: center;
    }

    .total-label {
        font-size: 1.1rem;
        margin-bottom: 10px;
    }

    .total-amount {
        font-size: 2rem;
        font-weight: 700;
    }
</style>
<div class="modal-dialog transaction-modal" style="width: 70%;">
    <div class="modal-content" >
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">{{$title}}</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="modal-info">
                    <div class="info-item">
                        <div class="info-label">Khách hàng</div>
                        <div class="info-value">👤 Nguyễn Văn A</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Trạng thái</div>
                        <div class="info-value"><span class="status completed">Hoàn thành</span></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ngày tạo</div>
                        <div class="info-value">📅 14/09/2024</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Số ngày</div>
                        <div class="info-value">🗓️ 2 ngày</div>
                    </div>
                </div>
                <div class="modal-days">
                    <div class="modal-day">
                        <div class="modal-day-header">
                            <span>📅 Ngày 1: Thứ Bảy, 15/09/2024</span>
                            <span>Tổng: 3,800,000đ</span>
                        </div>
                        <div class="modal-services">
                            <div class="modal-service">
                                <span class="service-name">🏨 Khách sạn Hilton - Phòng Deluxe</span>
                                <span class="service-price">2,500,000đ</span>
                            </div>
                            <div class="modal-service">
                                <span class="service-name">🍽️ Ăn trưa nhà hàng cao cấp</span>
                                <span class="service-price">800,000đ</span>
                            </div>
                            <div class="modal-service">
                                <span class="service-name">🚗 Xe đưa đón sân bay</span>
                                <span class="service-price">500,000đ</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-day">
                        <div class="modal-day-header">
                            <span>📅 Ngày 2: Chủ Nhật, 16/09/2024</span>
                            <span>Tổng: 1,500,000đ</span>
                        </div>
                        <div class="modal-services">
                            <div class="modal-service">
                                <span class="service-name">🎭 Tour Văn Miếu - Quốc Tử Giám</span>
                                <span class="service-price">300,000đ</span>
                            </div>
                            <div class="modal-service">
                                <span class="service-name">🍜 Ăn phở Hà Nội authentic</span>
                                <span class="service-price">200,000đ</span>
                            </div>
                            <div class="modal-service">
                                <span class="service-name">🛍️ Mua sắm phố cổ Hà Nội</span>
                                <span class="service-price">1,000,000đ</span>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <div class="modal-footer">
{{--            <a class="dt-modal hide click1"--}}
{{--               href="admin/transaction/view/{{$transaction->id}}?type={{$transaction->type}}" data-toggle="modal"--}}
{{--               data-target="#myModal"></a>--}}
            <button type="button" class="btn btn-default"
                    data-dismiss="modal">{{lang('dt_close')}}</button>
        </div>
    </div>
    </div>
</div>
<script>
    {{--expenseDropzone = initDropzone('.transaction_comment', '#comment-upload', {--}}
    {{--    previewTemplate: $("#preview-template").html(),--}}
    {{--    autoProcessQueue: false,--}}
    {{--    addRemoveLinks: true,--}}
    {{--    previewsContainer: '.dropzone-previews',--}}
    {{--    clickable: '.transaction_comment',--}}
    {{--    sending: function (file, xhr, formData) {--}}

    {{--    },--}}
    {{--    success: function (file, response) {--}}
    {{--        if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {--}}
    {{--        }--}}
    {{--    }--}}
    {{--});--}}

    {{--$("#comment-upload").validate({--}}
    {{--    rules: {},--}}
    {{--    messages: {},--}}
    {{--    submitHandler: function (form) {--}}
    {{--        var url = form.action;--}}
    {{--        var form = $(form),--}}
    {{--            formData = new FormData(),--}}
    {{--            formParams = form.serializeArray();--}}

    {{--        $.each(form.find('input[type="file"]'), function (i, tag) {--}}
    {{--            $.each($(tag)[0].files, function (i, file) {--}}
    {{--                formData.append(tag.name, file);--}}
    {{--            });--}}
    {{--        });--}}
    {{--        $.each(formParams, function (i, val) {--}}
    {{--            formData.append(val.name, val.value);--}}
    {{--        });--}}
    {{--        formData.append('transaction_id', {{$transaction->id}});--}}
    {{--        $.each(expenseDropzone.files, function (index, value) {--}}
    {{--            formData.append('file[]', value);--}}
    {{--        })--}}

    {{--        $.ajax({--}}
    {{--            url: url,--}}
    {{--            type: 'POST',--}}
    {{--            dataType: 'JSON',--}}
    {{--            cache: false,--}}
    {{--            contentType: false,--}}
    {{--            processData: false,--}}
    {{--            data: formData,--}}
    {{--        })--}}
    {{--            .done(function (data) {--}}
    {{--                if (data.result) {--}}
    {{--                    $(".content").val(' ');--}}
    {{--                    $(".dropzone-previews").html(' ');--}}
    {{--                    expenseDropzone.files = [];--}}
    {{--                    alert_float('success', data.message);--}}
    {{--                } else {--}}
    {{--                    alert_float('error', data.message);--}}
    {{--                }--}}
    {{--                $(".result_comment").html(data.html);--}}
    {{--            })--}}
    {{--            .fail(function (err) {--}}
    {{--            });--}}
    {{--        return false;--}}
    {{--    }--}}
    {{--});--}}

    {{--function editComment(comment_id){--}}
    {{--    $(`.edit_content_${comment_id}`).removeClass('hide');--}}
    {{--    $(`.content_${comment_id}`).addClass('hide');--}}
    {{--}--}}
    {{--function submitEdit(comment_id){--}}
    {{--    content = $(`.content_edit_${comment_id}`).val();--}}
    {{--    $.ajax({--}}
    {{--        url: 'admin/transaction/updateComment',--}}
    {{--        type: 'POST',--}}
    {{--        dataType: 'JSON',--}}
    {{--        cache: false,--}}
    {{--        data: {--}}
    {{--            comment_id: comment_id,--}}
    {{--            content: content--}}
    {{--        },--}}
    {{--    })--}}
    {{--        .done(function (data) {--}}
    {{--            if (data.result) {--}}
    {{--                alert_float('success', data.message);--}}
    {{--            } else {--}}
    {{--                alert_float('error', data.message);--}}
    {{--            }--}}
    {{--            $(".result_comment").html(data.html);--}}
    {{--        })--}}
    {{--        .fail(function () {--}}

    {{--        });--}}
    {{--    return false;--}}
    {{--}--}}
    {{--function deleteComment(comment_id){--}}
    {{--    var r = confirm("Bạn có chắc muốn xóa không ?");--}}
    {{--    if (r == false) {--}}
    {{--        return false;--}}
    {{--    } else {--}}
    {{--        $.ajax({--}}
    {{--            url: 'admin/transaction/deleteComment',--}}
    {{--            type: 'POST',--}}
    {{--            dataType: 'JSON',--}}
    {{--            cache: false,--}}
    {{--            data: {--}}
    {{--                comment_id: comment_id,--}}
    {{--                transaction_id: {{$transaction->id}}--}}
    {{--            },--}}
    {{--        })--}}
    {{--            .done(function (data) {--}}
    {{--                if (data.result) {--}}
    {{--                    alert_float('success', data.message);--}}
    {{--                } else {--}}
    {{--                    alert_float('error', data.message);--}}
    {{--                }--}}
    {{--                $(".result_comment").html(data.html);--}}
    {{--            })--}}
    {{--            .fail(function () {--}}

    {{--            });--}}
    {{--        return false;--}}
    {{--    }--}}
    {{--}--}}
</script>
