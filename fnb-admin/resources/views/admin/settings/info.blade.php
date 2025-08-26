<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        {{lang('c_info_company')}}
    </span>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="name_company">{{lang('c_name_company')}}</label>
        <input type="text" name="name_company" id="name_company"  value="{{get_option('name_company')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="address_company">{{lang('c_address_company')}}</label>
        <input type="text" name="address_company" id="address_company" value="{{get_option('address_company')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="phone_company">{{lang('c_phone_company')}}</label>
        <input type="text" name="phone_company" id="phone_company" value="{{get_option('phone_company')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="vat_company">{{lang('c_vat_company')}}</label>
        <input type="text" name="vat_company" id="vat_company" value="{{get_option('vat_company')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="vat_company">{{lang('c_email_company')}}</label>
        <input type="text" name="email_company" id="email_company" value="{{get_option('email_company')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="website_company">{{lang('c_website_company')}}</label>
        <input type="text" name="website_company" id="website_company" value="{{get_option('website_company')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="logo">{{lang('c_logo')}}</label>
        <input type="file" name="logo" id="logo" class="filestyle image" data-buttonbefore="true">
        <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
            <?php
                $imgLogo = get_option('logo');
                $imgLogo = !empty($imgLogo) ? $imgLogo : imgCameraDefault();
            ?>
            <img src="{{asset($imgLogo)}}" data-imgdefault="{{$imgLogo}}" alt="{{lang('c_logo')}}" class="img-responsive img-black" style="width: 150px;height: 150px">
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="logo">{{lang('c_favicon')}}</label>
        <input type="file" name="favicon" id="favicon" class="filestyle image" data-buttonbefore="true">
        <div style="display: flex;justify-content:center;margin-top: 5px" class="show_image">
            <?php
            $imgFavicon = get_option('favicon');
            $imgFavicon = !empty($imgFavicon) ? $imgFavicon : imgCameraDefault();
            ?>
            <img src="{{asset($imgFavicon)}}" data-imgdefault="{{$imgFavicon}}" alt="{{lang('c_favicon')}}" class="img-responsive img-black" style="width: 150px;height: 150px">
        </div>
    </div>
</div>
<div class="clearfix"></div>
<hr/>
<div class="title-statistic title_left">
    <span class="mleft5 mright5">
        {{lang('c_info_representative')}}
    </span>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="name_representative">{{lang('c_name_representative')}}</label>
        <input type="text" name="name_representative" id="name_representative"  value="{{get_option('name_representative')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="phone_representative">{{lang('c_phone_representative')}}</label>
        <input type="text" name="phone_representative" id="phone_representative"  value="{{get_option('phone_representative')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="email_representative">{{lang('c_email_representative')}}</label>
        <input type="text" name="email_representative" id="email_representative"  value="{{get_option('email_representative')}}" class="form-control">
    </div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="address_representative">{{lang('c_address_representative')}}</label>
        <input type="text" name="address_representative" id="address_representative"  value="{{get_option('address_representative')}}" class="form-control">
    </div>
</div>


