<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm OTP for SMB Membership Registration</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 15px; min-height:80vh; justify-items: center; align-items: center;">
<div style="max-width: 800px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); min-height: 50vh; justify-items: center; align-items: center;margin-top: 50px;">
    <div style="text-align: center; font-size: 24px; color: #333333; margin-bottom: 20px;margin-top: 10px;">
        <img src="{{url('/')}}/admin/assets/images/logo_new_smb.png" style="width: 120px">
    </div>
    <div style="text-align: center; font-size: 24px; color: #333333; margin-bottom: 20px;margin-top: 20px;">
        Verify OTP
    </div>
    <div style="font-size: 16px; color: #555555; text-align: center;">
        Thank you for using our service! Here is your OTP code:
    </div>
    <div style="font-size: 32px; font-weight: bold; color: #cd7400e0; text-align: center; margin: 20px 0;">
        {{$code}}
    </div>
    <div style="font-size: 16px; color: #555555; text-align: center; margin-bottom: 20px;">
        This OTP code is valid for {{get_option('time_otp')}} minutes. If you did not request this, please ignore this email.
    </div>
    <div style="font-size: 14px; color: #888888; text-align: center; margin-top: 20px;">
        © 2024 {{get_option('name_company')}}. All rights reserved. <br>
    </div>
</div>
</body>
</html>
