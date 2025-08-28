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

