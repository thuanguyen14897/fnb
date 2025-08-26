<div class="form-group">
    <label for="contact_phone">{{lang('c_contact_phone')}}</label>
    <input type="text" name="contact_phone" id="contact_phone"  value="{{get_option('contact_phone')}}" class="form-control">
</div>
<div class="form-group">
    <label for="contact_email">{{lang('c_contact_email')}}</label>
    <input type="text" name="contact_email" id="contact_email"  value="{{get_option('contact_email')}}" class="form-control">
</div>
<div class="form-group">
    <label for="contact_address_head_office">{{lang('c_contact_address_head_office')}}</label>
    <input type="text" name="contact_address_head_office" id="contact_address_head_office"  value="{{get_option('contact_address_head_office')}}" class="form-control">
</div>
<div class="form-group">
    <label for="contact_phone_head_office">{{lang('c_contact_phone_head_office')}}</label>
    <input type="text" name="contact_phone_head_office" id="contact_phone_head_office"  value="{{get_option('contact_phone_head_office')}}" class="form-control">
</div>
<div class="form-group">
    <label for="contact_address_branch_office">{{lang('c_contact_address_branch_office')}}</label>
    <input type="text" name="contact_address_branch_office" id="contact_address_branch_office"  value="{{get_option('contact_address_branch_office')}}" class="form-control">
</div>
<div class="form-group">
    <label for="contact_phone_branch_office">{{lang('c_contact_phone_branch_office')}}</label>
    <input type="text" name="contact_phone_branch_office" id="contact_phone_branch_office"  value="{{get_option('contact_phone_branch_office')}}" class="form-control">
</div>
<div class="form-group">
    <label for="contact_link_google_map">{{lang('c_contact_link_google_map')}}</label>
    <input type="text" name="contact_link_google_map" id="contact_link_google_map"  value="{{get_option('contact_link_google_map')}}" class="form-control">
</div>
<?php
if(!empty(get_option('contact_data_place_google_map'))) {?>
    <iframe
        width="100%"
        height="500px"
        style="border:0"
        loading="lazy"
        allowfullscreen
        referrerpolicy=""
        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyAuEM0-SakklPnXvLx-3aN1QbwEAOXSI4U&q=<?=get_option('contact_data_place_google_map')?>">
    </iframe>
<?php } ?>
<hr/>

<div class="form-group">
    <label for="link_contact_facebook">{{lang('c_link_facebook')}} <i class="fa fa-facebook"></i></label>
    <input type="text" name="link_contact_facebook" id="link_contact_facebook"  value="{{get_option('link_contact_facebook')}}" class="form-control">
</div>
<div class="form-group">
    <label for="link_contact_telegram">{{lang('c_link_contact_telegram')}} <i class="fa fa-telegram"></i></label>
    <input type="text" name="link_contact_telegram" id="link_contact_telegram"  value="{{get_option('link_contact_telegram')}}" class="form-control">
</div>
<div class="form-group">
    <label for="link_contact_zalo">{{lang('c_link_contact_x')}} <i class="fa fa-zalo"></i></label>
    <input type="text" name="link_contact_zalo" id="link_contact_zalo"  value="{{get_option('link_contact_zalo')}}" class="form-control">
</div>
<div class="form-group">
    <label for="content_short_footer">{{lang('c_content_short_footer')}}</label>
    <textarea name="content_short_footer" id="content_short_footer"  class="form-control">{{get_option('content_short_footer')}}</textarea>
</div>
<div class="form-group">
    <label for="copyright_footer">{{lang('c_copyright_footer')}} <i class="fa fa-copyright"></i></label>
    <input type="text" name="copyright_footer" id="copyright_footer"  value="{{get_option('copyright_footer')}}" class="form-control">
</div>



