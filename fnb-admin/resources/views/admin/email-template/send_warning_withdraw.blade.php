<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cảnh Báo Tài Khoản Sẽ Bị Khóa Sau 24h</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 15px; min-height:80vh; justify-items: center; align-items: center;">
<div style="max-width: 800px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); min-height: 50vh; justify-items: center; align-items: center;margin-top: 50px;">
    <div style="text-align: center; font-size: 24px; color: #333333; margin-bottom: 10px;margin-top: 10px;">
        <img src="{{url('/')}}/admin/assets/images/logo_new_smb.png" style="width: 120px">
    </div>
    {!! $content_warning_mail !!}
    <div style="font-size: 14px; color: #888888; text-align: center; margin-top: 20px;">
        © 2024 {{get_option('name_company')}}. Mọi quyền được bảo lưu. <br>
    </div>
</div>
</body>
</html>
