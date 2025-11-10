<!DOCTYPE html>
<html>
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 13px;
    }
    .container {
        display: flex;
        justify-content: center; /* Căn giữa theo chiều ngang */
        align-items: center; /* Căn giữa theo chiều dọc */
        height:100%; /* Đảm bảo container chiếm hết chiều cao của trang */
        background-image: url('admin/assets/images/background_qr.png');
        background-size: cover; /* Đảm bảo hình nền được phủ đầy */
        background-position: center; /* Đảm bảo hình nền được căn giữa */
    }

    .qr-code{
        margin-top: 0px;
        margin-left: 65px;
    }
</style>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
</head>
<body>
<div class="container">
    <div style="text-align: center;margin-left:50px;font-size:15px;padding-top: 150px;margin-bottom: 5px;width: 200px;">{{$title_business}}</div>
    <img class="qr-code" src="{{ $qrCodeDataUri }}" alt="QR Code">
    <div style="text-align: center;margin-left:50px;font-size:20px;padding-top: 65px;width: 200px;">{{$referral_code}}</div>
    <div style="text-align: center;margin-left:10px;font-size:15px;padding-top: 5px;width: 300px;">Quét mã QR để trở thành thành viên</div>
</div>
</body>
</html>
