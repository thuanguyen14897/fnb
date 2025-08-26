<!DOCTYPE html>
<html lang="{{\Illuminate\Support\Facades\Lang::locale()}}">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
    <meta name="author" content="Coderthemes">
    <!-- App Favicon icon -->
    <link href="{{get_option('favicon')}}" rel="shortcut icon">
    <!-- App Title -->
    <title>{{get_option('name_company')}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ asset('') }}">


    <link href="admin/assets/plugins/bootstrap-tagsinput/css/bootstrap-tagsinput.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/switchery/css/switchery.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/multiselect/css/multi-select.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/select2/css4/select2.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css" rel="stylesheet"/>
    <link href="admin/assets/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="admin/assets/plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/dataTables.colVis.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/datatables/fixedColumns.dataTables.min.css" rel="stylesheet" type="text/css"/>
    <!-- Ladda buttons css -->
    <link href="admin/assets/plugins/ladda-buttons/css/ladda-themeless.min.css" rel="stylesheet" type="text/css"/>

    <link href="admin/assets/plugins/ion-rangeslider/ion.rangeSlider.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/ion-rangeslider/ion.rangeSlider.skinFlat.css" rel="stylesheet" type="text/css"/>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.3.7/jquery.datetimepicker.min.css"/>
    <!-- Bootstrap Slider -->
    <link href="admin/assets/plugins/bootstrap-slider/css/bootstrap-slider.min.css" rel="stylesheet" type="text/css"/>

    <!--Morris Chart CSS -->
    <link rel="stylesheet" href="admin/assets/plugins/morris/morris.css">
    <link href="admin/assets/plugins/dropzone/dropzone.css" rel="stylesheet" type="text/css">

    <link href="admin/assets/plugins/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="admin/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/core.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/style.css?v=1.1" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/call_center.css?v=1.1" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/components.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/icons.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/pages.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/menu.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/css/responsive.css" rel="stylesheet" type="text/css"/>
    <link href="admin/assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="admin/assets/plugins/slick/css/slick.css"/>
    <link rel="stylesheet" type="text/css" href="admin/assets/plugins/slick/css/slick-theme.css"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="admin/assets/js/modernizr.min.js"></script>

</head>
<body>
<video id="my-video" style="display: none;" autoplay playsinline muted></video>
<video id="peer-video" style="display: none;" autoplay playsinline></video>
<!-- Navigation Bar-->
<header id="topnav">
    @include('admin.layouts.header')
</header>
<!-- End Navigation Bar-->
<div class="wrapper">
    <section id="loading">
        <img class="loading_img hide" src="admin/assets/images/loading.png"/>
        <div id="loading-content"></div>
    </section>
    <div id="toast-container-new" class="toast-top-right-new"></div>
    <!-- content -->
    <div class="container">
        <div class="content-call-center" id="draggable-call">
            @include('admin.layouts.call_center')
        </div>
        <div id="draggable-driver">
            <div class="card-call-center hide" style="display: flex;justify-content: center">
                <div class="btn_xac_nhan" style="display: flex;align-items: center;color: white;cursor: pointer"></div>
                <div class="driver_id"></div>
            </div>
        </div>
        @yield('content')
        <div class="modal fade" id="dtModal" role="dialog" aria-labelledby="myModalLabel"></div>
        <div class="modal fade" style="z-index: 999999999999" id="dtModal2" role="dialog"
             aria-labelledby="myModalLabel"></div>
        <div id="data_profile"></div>
        <!-- endcontent -->
        <!-- Footer -->
        <footer class="footer text-right">
            <div class="container">
                <div class="row">
                    <div class="col-xs-6">
                        © {{date('Y')}}. BY COMPANY FOSO
                    </div>
                    <div class="col-xs-6 hide">
                        <ul class="pull-right list-inline m-b-0">
                            <li>
                                <a href="#">About</a>
                            </li>
                            <li>
                                <a href="#">Help</a>
                            </li>
                            <li>
                                <a href="#">Contact</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        <!-- End Footer -->
    </div> <!-- end container -->
    <div id="preview-template" style="display: none">
        <div class="dz-preview dz-file-preview">
            <div class="dz-image"><img data-dz-thumbnail=""></div>
            <div class="dz-details">
                <div class="dz-size"><span data-dz-size=""></span></div>
                <div class="dz-filename"><span data-dz-name=""></span></div>
            </div>
            <div class="dz-progress"><span class="dz-upload"
                                           data-dz-uploadprogress=""></span></div>
            <div class="dz-error-message"><span data-dz-errormessage=""></span></div>
        </div>
    </div>
</div>
<div id="user_list"></div>
<!-- end wrapper -->
{!! Notify::render() !!}

<!-- jQuery  -->
<script src="admin/assets/js/jquery.min.js"></script>
<script src="admin/assets/js/bootstrap.min.js"></script>
<script src="admin/assets/js/detect.js"></script>
<script src="admin/assets/js/fastclick.js"></script>
<script src="admin/assets/js/jquery.slimscroll.js"></script>
<script src="admin/assets/js/jquery.blockUI.js"></script>
<script src="admin/assets/js/waves.js"></script>
<script src="admin/assets/js/wow.min.js"></script>
<script src="admin/assets/js/jquery.nicescroll.js"></script>
<script src="admin/assets/js/jquery.scrollTo.min.js"></script>

<script src="admin/assets/plugins/accounting/accounting.min.js"></script>
<script src="admin/assets/plugins/bootstrap-filestyle/js/bootstrap-filestyle.min.js" type="text/javascript"></script>

<script src="admin/assets/plugins/switchery/js/switchery.min.js"></script>
<script type="text/javascript" src="admin/assets/plugins/multiselect/js/jquery.multi-select.js"></script>
<script type="text/javascript" src="admin/assets/plugins/jquery-quicksearch/jquery.quicksearch.js"></script>
{{--<script src="admin/assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>--}}
<script src="admin/assets/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
<script src="admin/assets/plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js"
        type="text/javascript"></script>
<script src="admin/assets/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js" type="text/javascript"></script>
<script type="text/javascript" src="admin/assets/pages/jquery.form-advanced.init.js"></script>


<script src="admin/assets/plugins/peity/jquery.peity.min.js"></script>
<script src="admin/assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
<script src="admin/assets/plugins/counterup/jquery.counterup.min.js"></script>
{{--<script src="admin/assets/plugins/select2/js/select2.js"></script>--}}
<script src="admin/assets/plugins/morris/morris.min.js"></script>
<script src="admin/assets/plugins/raphael/raphael-min.js"></script>

<script src="admin/assets/plugins/jquery-knob/jquery.knob.js"></script>

<script src="admin/assets/plugins/validate/js/jquery.validate.min.js"></script>
<!-- Parsly js -->
<script type="text/javascript" src="admin/assets/plugins/parsleyjs/parsley.min.js"></script>

<script
    src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>

<!-- App core js -->


<script src="admin/assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.bootstrap.js"></script>

<script src="admin/assets/plugins/datatables/dataTables.buttons.min.js"></script>
<script src="admin/assets/plugins/datatables/buttons.bootstrap.min.js"></script>
<script src="admin/assets/plugins/datatables/jszip.min.js"></script>
<script src="admin/assets/plugins/datatables/pdfmake.min.js"></script>
<script src="admin/assets/plugins/datatables/vfs_fonts.js"></script>
<script src="admin/assets/plugins/datatables/buttons.html5.min.js"></script>
<script src="admin/assets/plugins/datatables/buttons.print.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.fixedHeader.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.keyTable.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.responsive.min.js"></script>
<script src="admin/assets/plugins/datatables/responsive.bootstrap.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.scroller.min.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.colVis.js"></script>
<script src="admin/assets/plugins/datatables/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/api/sum().js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-rowgroup/1.1.1/dataTables.rowGroup.js"
        integrity="sha512-wsX6fHyrivQpBEd2DP7Lze2cmmsCFdeQCEiSotbbGnxlfJfUxIv4WoKhE49rMm2yBj+yZUZJRKLEMSMxUOYxjQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="admin/assets/pages/datatables.init.js"></script>
{{--picker--}}
<script src="admin/assets/plugins/moment/moment.js"></script>
<script src="admin/assets/plugins/timepicker/bootstrap-timepicker.js"></script>
<script src="admin/assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
<script src="admin/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="admin/assets/plugins/clockpicker/js/bootstrap-clockpicker.min.js"></script>
<script src="admin/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script src="admin/assets/pages/jquery.form-pickers.init.js"></script>
<!-- ladda js -->
<script src="admin/assets/plugins/ladda-buttons/js/spin.min.js"></script>
<script src="admin/assets/plugins/ladda-buttons/js/ladda.min.js"></script>
<script src="admin/assets/plugins/ladda-buttons/js/ladda.jquery.min.js"></script>
{{--pusher--}}
<script src="https://js.pusher.com/4.4/pusher.min.js"></script>
<!-- Notification js -->
<script src="admin/assets/plugins/notifyjs/js/notify.js"></script>
<script src="admin/assets/plugins/notifications/notify-metro.js"></script>
<script src="admin/assets/plugins/dropzone/dropzone.js"></script>

<script src="admin/assets/plugins/lightbox/js/lightbox.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.5.0/tinymce.min.js"
        referrerpolicy="origin"></script>

<script src="admin/assets/plugins/ion-rangeslider/ion.rangeSlider.min.js"></script>
<script src="admin/assets/plugins/bootstrap-slider/js/bootstrap-slider.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script src="admin/assets/js/app.js?v=1.2"></script>
<script src="admin/assets/js/plugnis.js?v=1.0"></script>
<script src="admin/assets/js/jquery.core.js"></script>
<script src="admin/assets/js/jquery.app.js"></script>
<script src="admin/assets/plugins/select2/js4/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>

<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="admin/assets/plugins/slick/js/slick.min.js"></script>

<script src="https://code.highcharts.com/highcharts.js"></script>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        mainWrapperHeightFix();
        $('.counter').counterUp({
            delay: 100,
            time: 1200
        });

        $(".knob").knob();

    });
</script>
<script type="text/javascript">
    TableManageButtons.init();
    <?php $length_table = get_option('length_table');?>
    var options = {
        tables_pagination_limit: "<?= !empty($length_table) ? $length_table : '15' ?>",
        scroll_responsive_tables: 1,
        decimal_places: 2,
        thousands_sep: '{{get_option('thousands_sep')}}',
        decimals_money: '{{get_option('decimals_money')}}',
        decimals_number: '{{get_option('decimals_number')}}',
        decimals_sep: '{{get_option('decimals_sep')}}',
    };
    var lang = {
        datatables: <?php echo json_encode(AppHelper::get_datatables_language_array()); ?>,
        dt_length_menu_all: "<?php echo lang('dt_length_menu_all'); ?>",
        dt_button_export: "<?php echo lang('dt_button_export'); ?>",
        dt_button_excel: "<?php echo lang('dt_button_excel'); ?>",
        dt_button_csv: "<?php echo lang('dt_button_csv'); ?>",
        dt_button_pdf: "<?php echo lang('dt_button_pdf'); ?>",
        dt_button_print: "<?php echo lang('dt_button_print'); ?>",
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
{{--ckeditor--}}
<script src="admin/ckeditor/ckeditor.js"></script>
<script>
    $( function() {
        $( "#draggable-call" ).draggable();
        $( "#draggable-driver" ).draggable();
    });
    var notificationsWrapper = $('.dropdown-menu-lg');
    var notificationsCountElem = $('.navbar-c-items').find('a > span[data-count]');
    var notificationsCount = parseInt(notificationsCountElem.data('count'));
    var notifications = notificationsWrapper.find('div.div-data-noti');
    pusher_key = "{{get_option('pusher')}}";
    Pusher.logToConsole = true;

    var pusher = new Pusher(`${pusher_key}`, {
        cluster: 'ap1'
    });
    var channel = pusher.subscribe('notifications-channel-<?php echo get_staff_user_id(); ?>-staff');
    channel.bind('notification', function (data) {
        // loadNoti();
        var classes = '';
        var href = '';
        json_data = JSON.parse(data.json_data);
        if (json_data.object != undefined) {
            if (json_data.object == 'transaction') {
                classes = 'dt-modal';
                href = `href=admin/transaction/view/${data.object_id}?type=${json_data.type}`;
            } else if(json_data.object == 'transaction_driver') {
                classes = 'dt-modal';
                href = `href=admin/transaction_driver/view/${data.object_id}?type=${json_data.type}`;
            }
        }
        var existingNotifications = notifications.html();
        var newNotificationHtml = `<a ${href} class="list-group-item ${classes}" style="background: aliceblue">
            <div class="media">
                    <div class="pull-left p-r-10">
                        <em onclick=readSingleNoti(this,${data.id}) style="cursor: pointer" class="fa fa-bell-o noti-custom-not-read"></em>
                    </div>
                    <div class="media-body">
                        <h5 class="media-heading">${data.title}</h5>
                        <p class="m-0">
                            <small>${data.content}</small>
                        </p>
                        <p class="m-0" style="color: #8496AE">
                            <small>${data.created_at_new}</small>
                        </p>
                    </div>
                </div>
            </a>`;
        notifications.html(newNotificationHtml + existingNotifications);

        notificationsCount += 1;
        notificationsCountElem.attr('data-count', notificationsCount);
        notificationsCountElem.text(notificationsCount);
        notificationsWrapper.find('li.notifi-title > span').text('New ' + notificationsCount);
        json_data = JSON.parse(data.json_data);
        if (Notification.permission !== 'granted') {
            Notification.requestPermission();
        } else {
            var notification = new Notification(`${data.title}`, {
                icon: "{{asset('').get_option('favicon')}}",
                body: data.content,
            });
            if (json_data.object == 'transaction') {
                notification.onclick = function () {
                    window.open(`{{asset('')}}admin/transaction/list?type=${json_data.type}`);
                };
            }
        }
    });
    channel.bind('reward_day', function (data) {
        console.log(1);
    });
    // test app driver
    {{--var channelNew = pusher.subscribe('notifications-channel-<?php echo get_staff_user_id() == 1 ? 1 : 2; ?>-driver');--}}
    {{--channelNew.bind('booking-driver', function(data) {--}}
    {{--    getListTransactionNotDriver(<?php echo get_staff_user_id() == 1 ? 1 : 2; ?>);--}}
    {{--});--}}
    {{--channelNew.bind('accpet-driver', function(data) {--}}
    {{--    getListTransactionNotDriver(<?php echo get_staff_user_id() == 1 ? 1 : 2; ?>);--}}
    {{--});--}}
    {{--channelNew.bind('auto-accpet-driver', function(data) {--}}
    {{--    $("#draggable-driver").find('.card-call-center').removeClass('hide');--}}
    {{--    html = `<div onclick="startTrip(${data.id},${data.driver_id})">Khởi hành chuyến ${data.reference_no}</div>--}}
    {{--            <div onclick="cancelTrip(${data.id},${data.driver_id})">Hủy chuyến chuyến ${data.reference_no}</div>`;--}}
    {{--    $("#draggable-driver").find('.card-call-center').find('.btn_xac_nhan').html(html);--}}
    {{--});--}}

    function cancelTrip(transaction_id = 0,driver_id = 0){
        $.ajax({
            type: "POST",
            url: 'api/transaction_driver/changeStatus',
            data: {
                status : 4,
                type_driver:1,
                transaction_id:transaction_id,
                driver_id:driver_id,
                note:"Tài xế có việc bận"
            },
            headers: {
                "Cache-Control": "no-cache",
                "Authorization": "Bearer " + 'kanow',
            },
            dataType: "json",
            success: function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                    $("#draggable-driver").find('.card-call-center').addClass('hide');
                } else {
                    alert_float('error', data.message);
                }
            }
        });
    }

    function confirmTrip(transaction_id,driver_id){
        $.ajax({
            type: "POST",
            url: 'api/driver/confirmTrip',
            data: {
                transaction_id: transaction_id,
                driver_id: driver_id,
            },
            headers: {
                "Cache-Control": "no-cache",
                "Authorization": "Bearer " + 'NTN8fHxleUpwZGlJNklsVktkbkYwZWxselZDdFhORFZ5UXpWdFdtaFpaRkU5UFNJc0luWmhiSFZsSWpvaU9XeFJWekpKTWtaelkwVnFNVlZUYlU1RlNHZHVNaXRvVkdWR2NUZDNUSE15WldaVmVHUldURVI1U1QwaUxDSnRZV01pT2lKbE9Ua3hPRGM0TmpBd056bG1OREV4TWpBNU9HSXhOalE1WVdGa05UQXlZVGN5WkdZNVpUUTFZbVEzTVdKaU4yTmtObVF4WWpNMk1HWXdOV05sWXpSa0lpd2lkR0ZuSWpvaUluMD18fHwyMDI0LTA3LTMxIDE1OjM4OjExfHx8fHx8fHx8',
            },
            dataType: "json",
            success: function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
            }
        });
    }

    function getListTransactionNotDriver(driver_id){
        $.ajax({
            type: "GET",
            url: 'api/driver/getListTransactionNotDriver',
            data: {
                driver_id: driver_id,
            },
            headers: {
                "Cache-Control": "no-cache",
                "Authorization": "Bearer " + 'NTN8fHxleUpwZGlJNklsVktkbkYwZWxselZDdFhORFZ5UXpWdFdtaFpaRkU5UFNJc0luWmhiSFZsSWpvaU9XeFJWekpKTWtaelkwVnFNVlZUYlU1RlNHZHVNaXRvVkdWR2NUZDNUSE15WldaVmVHUldURVI1U1QwaUxDSnRZV01pT2lKbE9Ua3hPRGM0TmpBd056bG1OREV4TWpBNU9HSXhOalE1WVdGa05UQXlZVGN5WkdZNVpUUTFZbVEzTVdKaU4yTmtObVF4WWpNMk1HWXdOV05sWXpSa0lpd2lkR0ZuSWpvaUluMD18fHwyMDI0LTA3LTMxIDE1OjM4OjExfHx8fHx8fHx8',
            },
            dataType: "json",
            success: function (data) {
                if (data.data.length > 0) {
                    $("#draggable-driver").find('.card-call-center').removeClass('hide');
                    html = '';
                    $.each(data.data,function (k,v){
                        html += `<div onclick="confirmTrip(${v.id},<?php echo get_staff_user_id() == 1 ? 1 : 2; ?>)">Nhận chuyến ${v.reference_no}</div>`;
                    });
                    $("#draggable-driver").find('.card-call-center').find('.btn_xac_nhan').html(html);
                } else {
                    $("#draggable-driver").find('.card-call-center').addClass('hide');
                }
            }
        });
    }
    //end
    pageNoti = 1;
    isCall = 1;

    function loadNoti() {
        $.ajax({
            url: 'admin/notification/loadNoti',
            type: 'POST',
            dataType: 'html',
            cache: false,
            data: {
                pageNoti: pageNoti,
            },
        }).done(function (data) {
            $(".div-data-noti").html(data);
            if ($('.slimscroll-noti') > $('.div-data-noti').height()) {
                loadMoreNoti();
            }
        }).fail(function () {
        })
    }

    function loadMoreNoti() {
        next = $(".next_noti").val();
        if (next == 0) {
            return;
        }
        pageNoti++
        $.ajax({
            type: "POST",
            url: 'admin/notification/loadMoreNoti',
            data: {
                page: pageNoti,
            },
            dataType: "html",
            success: function (data) {
                if (data) {
                    $(`.div-data-noti`).append(data);
                    if ($('.slimscroll-noti') > $('.div-data-noti').height()) {
                        loadMoreNoti();
                    }
                }
            }
        });
    }

    $(".clickNoti").click(function () {
        if (isCall == 1) {
            loadNoti();
            isCall = 0;
        }
        if ($(".keep-inside-clicks-open").hasClass('show')) {
            isCall = 0;
        } else {
            isCall = 1;
        }
    })
    $('.slimscroll-noti').scroll(function () {
        if ($('.slimscroll-noti').scrollTop() >= ($('.div-data-noti').height() - $('.slimscroll-noti').height())) {
            loadMoreNoti();
        }
    });

    function readSingleNoti(_this, id) {
        $.ajax({
            type: "POST",
            url: 'admin/notification/readSingleNoti',
            data: {
                notification_id: id,
            },
            dataType: "json",
            success: function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                    $(_this).closest('a.list-group-item').css({
                        background: "white"
                    })
                    $(_this).closest('a.list-group-item').find('em.fa-bell-o').removeClass('noti-custom-not-read');
                    $(_this).closest('a.list-group-item').find('em.fa-bell-o').addClass('noti-custom');
                } else {
                    alert_float('error', data.message);
                }
            }
        });
    }

    function readAllNoti(_this) {
        $.ajax({
            type: "POST",
            url: 'admin/notification/readAllNoti',
            data: {
                'type': 'staff'
            },
            dataType: "json",
            success: function (data) {
                if (data.result) {
                    alert_float('success', data.message);
                    $(".div-data-noti").html('');
                    loadMoreNoti();
                } else {
                    alert_float('error', data.message);
                }
            }
        });
    }

    function notifyMe() {
        if (Notification.permission !== 'granted') {
            Notification.requestPermission();
        } else {
            var notification = new Notification('Notification title', {
                icon: "{{asset('').get_option('favicon')}}",
                body: 'Hey there! You\'ve been notified!',
            });
            notification.onclick = function () {
                window.open('{{asset('')}}admin/transaction/list?type=1');
            };
        }
    }
</script>
<script>
    // const pusherNew = new Pusher(
    //     pusher_key,
    //     {
    //         cluster: 'ap1', // Replace with 'cluster' from dashboard
    //         forceTLS: true,
    //         channelAuthorization: {
    //             endpoint: "http://192.168.1.178:8080/api/channel/authorize?type=web",
    //         }
    //     }
    // );
    // const hashCode = (s) =>
    //     s.split("").reduce((a, b) => {
    //         a = (a << 5) - a + b.charCodeAt(0);
    //         return a & a;
    //     }, 0);
    // function addMemberToUserList(memberId) {
    //     userEl = document.createElement("div");
    //     userEl.id = "user_" + memberId;
    //     userEl.innerText = memberId;
    //     userEl.style.backgroundColor =
    //         "hsl(" + (hashCode(memberId) % 360) + ",70%,60%)";
    //     document.getElementById("user_list").appendChild(userEl);
    // }
    // const channelNew = pusherNew.subscribe("presence-quickstart");
    // channelNew.bind("pusher:subscription_succeeded", () =>
    //     channelNew.members.each((member) => addMemberToUserList(member.id))
    // );
    // channelNew.bind("pusher:member_added", (member) => {
    //     addMemberToUserList(member.id);
    // });
    // channelNew.bind("pusher:member_removed", (member) => {
    //     const userEl = document.getElementById("user_" + member.id);
    //     userEl.parentNode.removeChild(userEl);
    // });
    // var pusher_presence = new Pusher(pusher_key, {
    //     authEndpoint: 'http://192.168.1.178:8080/api/channel/authorize',
    //     authTransport: 'jsonp',
    //     'cluster': 'ap1',
    // });
    // var presenceChannel = pusher_presence.subscribe('presence-mychanel');
    // /*--------------- Pusher Trigger user connected ---------------*/
    // presenceChannel.bind('pusher:member_added', function (data) {
    //    console.log(data);
    // });
    // /*--------------- Pusher Trigger user logout ---------------*/
    // presenceChannel.bind('pusher:member_removed', function (data) {
    // });

</script>
{{--<script src="https://capi.caresoft.vn/js/embed/jssip-3.2.10.js" type="text/javascript"></script>--}}
{{--<script src="https://capi.caresoft.vn/js/embed/init-3.0.7.js" type="text/javascript"></script>--}}
{{--<script src="https://capi.caresoft.vn/js/embed/web.push.js" type="text/javascript"></script>--}}
{{--<script src="https://capi.caresoft.vn/js/embed/cs_const.js" type="text/javascript"></script>--}}
{{--<script src="https://capi.caresoft.vn/js/embed/cs_voice.js" type="text/javascript"></script>--}}
{{--<script src="https://capi.caresoft.vn/js/embed/custom.js" type="text/javascript"></script>--}}

{{--<script src="admin/assets/js/handle_voice_script.js?v=1.0"></script>--}}
<script type="text/javascript">
    var interval;
    let tokenVoice = localStorage.getItem('tokenVoice');
    tokenVoice = $.parseJSON(tokenVoice);
    token = tokenVoice != null ? tokenVoice.token : null;
    if(token != null) {
        // csInit(token, 'kanow');
    }
    function onTransferCallSurvey() {
        transferSurvey({id: 'survey_id', sipurl: 'surveySipURL'});
    }

    function transferCall() {
        getTransferAgent();
        csTransferCallAgent('5000');
    }

    function transferCallToAcd() {
        csTransferCallAcd('QUEUE_ID');
    }


    function onCallout(_this) {
        phone_customer = $(_this).find('.phone_customer').html();
        // phone_customer = '0772818495';
        const calloutId = document.getElementById('select-call-out-id').value;
        if(csVoice.enableVoice == true){

        } else {
            alert_float('error','Vui lòng kích hoạt thoại !');
            return;
        }
        $.ajax({
            url: 'admin/call_center/updateContactCallCenter',
            type: 'POST',
            dataType: 'JSON',
            cache: false,
            data: {
                phone_customer: phone_customer
            },
        })
            .done(function (data) {
                if (calloutId) {
                    console.log('calling with callout service: ', calloutId);
                    csCallout(phone_customer, calloutId);

                } else {
                    console.log('calling with default callout service');
                    csCallout(phone_customer);
                }
            })

    }
</script>
@yield('script')
</body>
</html>

