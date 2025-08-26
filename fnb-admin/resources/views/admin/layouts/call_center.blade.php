<div class="card-call-center hide">
    <div class="header-call-center">
        <div class="animation-call-center">
            <span class="icon ring"></span>
            <div class="cercle one"></div>
            <div class="cercle two"></div>
            <div class="cercle three"></div>
        </div>

        <p class="phoneNumber"></p>
        <p class="calling">Calling</p>
    </div>

    <div class="footer-call-center">
    </div>
</div>
<script>
    function timeCall(){
        var time = 0;
        var minute = 0;
        var timeZero;
        var minuteZero;
        result = '';
        $(".card-call-center").find('.calling').html(' ');
        interval = setInterval(function (){
            time += 1;
            if(time >= 60){
                minute += 1;
                time = 0;
            }
            if(minute < 10){
                minuteZero = '0';
            } else {
                minuteZero = '';
            }
            if(time < 10){
                timeZero = '0';
            } else {
                timeZero = '';
            }
            result = minuteZero+minute+':'+timeZero+time;
            $(".card-call-center").find('.calling').html(result);
        },1000);
    }
</script>
