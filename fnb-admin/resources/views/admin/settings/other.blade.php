<div class="form-group">
    <label for="title_package">Tiêu đề gói thành viên</label>
    <input type="text" name="title_package" id="title_package" value="{{get_option('title_package')}}" class="form-control">
</div>
<div class="form-group">
    <label for="content_package">Mô tả gói thành viên</label>
    <textarea class="content_package form-control"
              name="content_package">{{get_option('content_package')}}</textarea>
</div>
<div class="form-group">
    <label for="percent">Chiết khấu</label>
    <input type="number" name="percent" id="percent" value="{{get_option('percent')}}" min="0" max="100" onkeyup="formatNumBerKeyChange(this)" class="form-control">
</div>
<div class="form-group">
    <label for="intro_one">Nội dung intro 1</label>
    <input type="text" name="intro_one" id="link_url_index" value="{{get_option('intro_one')}}" class="form-control">
</div>
<div class="form-group">
    <label for="intro_two">Nội dung intro 2</label>
    <input type="text" name="intro_two" id="intro_two" value="{{get_option('intro_two')}}" class="form-control">
</div>
<div class="form-group">
    <label for="intro_three">Nội dung intro 3</label>
    <input type="text" name="intro_three" id="intro_three" value="{{get_option('intro_three')}}" class="form-control">
</div>
<div class="form-group">
    <label for="version_app">Version App</label>
    <input type="text" name="version_app" id="version_app"  value="{{(get_option('version_app'))}}" class="form-control">
</div>
<div class="form-group">
    <label for="version_app_android">Version App Android</label>
    <input type="text" name="version_app_android" id="version_app_android"  value="{{(get_option('version_app_android'))}}" class="form-control">
</div>
<div class="form-group">
    <label for="note_version_app">Ghi chú version app</label>
    <textarea class="note_version_app form-control"
              name="note_version_app">{{get_option('note_version_app')}}</textarea>
</div>
<div class="form-group">
    <label for="rule_register_partner">Điều khoản đăng ký trở thành đối tác </label>
    <textarea class="rule_register_partner form-control editor"
              name="rule_register_partner">{{get_option('rule_register_partner')}}</textarea>
</div>
<div class="form-group">
    <label for="terms_guide">Hướng dẫn điền điều khoản </label>
    <textarea class="terms_guide form-control editor"
              name="terms_guide">{{get_option('terms_guide')}}</textarea>
</div>
<div class="form-group">
    <label for="policy_terms">Điều khoản và chính sách</label>
    <textarea class="policy_terms form-control editor"
              name="policy_terms">{{get_option('policy_terms')}}</textarea>
</div>
<div class="form-group">
    <label for="length_table">{{lang('c_length_table')}}</label>
    <input type="text" name="length_table" id="length_table"  value="{{get_option('length_table')}}" class="form-control">
</div>

<div class="form-group">
    <label for="google_api_key">{{lang('c_google_api_key')}}</label>
    <input type="text" name="google_api_key" id="google_api_key"  value="{{get_option('google_api_key')}}" class="form-control">
</div>
<hr/>

