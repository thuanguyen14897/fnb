window.onbeforeunload = function (e) {
    return 'abc';
};

window.unload = function () {
    csUnregister();
    if (csVoice.enableVoice) {
        reConfigDeviceType();
    }
    return "1";
}

// kết thúc cuộc gọi ra/vào
function csEndCall() {
    console.log("Call is ended");
    console.log(csVoice);
    // if (csVoice.hasCall == false) {
        $(".card-call-center").find('.phoneNumber').html(' ');
        $(".card-call-center").find('.calling').html(' ');
        $(".card-call-center").addClass('hide');

        $(".wrap_call").removeClass('hide');
        $(".wrap_calling").addClass('hide');

        if (interval !== undefined){
            clearInterval(interval);
        }
    // }
}

// đổ chuông trình duyệt của agent khi gọi vào
// đổ chuông tới khách hàng khi gọi ra
function csCallRinging(phone) {
    console.log("Has a new call to customer: " + phone);
    console.log(csVoice);
    $(".card-call-center").removeClass('hide');
    $(".card-call-center").find('.calling').html('Calling');
    $(".card-call-center").find('.phoneNumber').html(phone);
    loadHtmlCallCenter(1,csVoice);
}

// cuộc gọi vào được agent trả lời
function csAcceptCall() {
    console.log("Call is Accepted");
    console.log(csVoice);
    loadHtmlCallCenter(2,csVoice);

}

// cuộc gọi ra được khách hàng trả lời
function csCustomerAccept() {
    console.log("csCustomerAccept");
    loadHtmlCallCenter(3,csVoice);
}

function csMuteCall() {
    console.log("Call is muted");
    $(".call_mute").find('.icon-unmute').addClass('hide');
    $(".call_mute").find('.icon-mute').removeClass('hide');
}

function csUnMuteCall() {
    console.log("Call is unmuted")
    $(".call_mute").find('.icon-unmute').removeClass('hide');
    $(".call_mute").find('.icon-mute').addClass('hide');
}

function csHoldCall() {
    console.log("Call is holded");
    $(".call_hold").find('.icon-unhold').addClass('hide');
    $(".call_hold").find('.icon-hold').removeClass('hide');
}

function csUnHoldCall() {
    console.log("Call is unholded");
    $(".call_hold").find('.icon-unhold').removeClass('hide');
    $(".call_hold").find('.icon-hold').addClass('hide');
}

function showCalloutInfo(number) {
    console.log("callout to " + number);
}

function showCalloutError(errorCode, sipCode) {
    console.log("callout error " + errorCode + " - " + sipCode);
}

function csShowEnableVoice(enableVoice) {
    console.log(`Voice active status : ${enableVoice}`);
    if (enableVoice) {
        document.getElementById('enable').setAttribute('disabled', 'disabled');
        // alert_float('success','Kích hoạt thoại call center thành công!');
    } else {
        document.getElementById('enable').removeAttribute('disabled');
        // alert_float('error','Thoại call center đã bị tắt!');
        // csEnableCall();
    }
}

function csShowDeviceType(type) {
    console.log(`Current device: ${type}`);
}

function csShowCallStatus(status) {
    console.log("csShowCallStatus");
    if (status == 'Online'){
        color = 'green';
    } else {
        color = '#8C8A8C';
        changeCallStatus();
    }
    document.getElementById('onOffIncicator').innerHTML = '<div style="color: '+color+'"><span><i class="fa fa-circle"></i></span> '+status+'</div>';
}

function csInitComplete() {
    console.log("csInitComplete");
    if (!csVoice.enableVoice) {
        csEnableCall();
    }
    if (csVoice.deviceType !=1) {
        changeDevice(1);
    }
    const lstCallout = csVoice.getCalloutServices();
    const defaultCalloutId = (lstCallout.find(c => c.is_default == 1) || {}).callout_id;
    $("#select-call-out-id").html(' ');
    const selectEl = document.querySelector("#select-call-out-id");
    lstCallout.forEach(c => {
        const calloutOptions = document.createElement("option");
        calloutOptions.value = c.callout_id;
        calloutOptions.text = c.descriptions;
        selectEl.appendChild(calloutOptions);
    });

    if (defaultCalloutId) {
        selectEl.value = defaultCalloutId;
    }
}

function csCurrentCallId(callId) {
    console.log("csCurrentCallId: " + callId);
}

function csInitError(error) {
    console.log("csInitError: " + error);
}

function csListTransferAgent(listTransferAgent) {
    console.log(listTransferAgent);
}

function csTransferCallError(error, tranferedAgentInfo) {
    console.log('Transfer call failed,' + error);
}

function csTransferCallSuccess(tranferedAgentInfo) {
    console.log('transfer call success');
}

function csNewCallTransferRequest(transferCall) {
    console.log('new call transfer');
    console.log(transferCall);
    document.getElementById('phoneNo').innerHTML = transferCall.dropAgentId + ' chuyển cg cho bạn';
    document.getElementById('transferResponseOK').removeAttribute('disabled');
    document.getElementById('transferResponseReject').removeAttribute('disabled');
}

function csTransferCallResponse(status) {
    document.getElementById('transferResponseOK').setAttribute('disabled', 'disabled');
    document.getElementById('transferResponseReject').setAttribute('disabled', 'disabled');
    console.log(status);
}

function csNotifyReconnecting(noretry, totalRetry) {
    console.log('reconnecting from custom js......');
}

function csOndisconnected() {
    console.log('disconnected from custom js !!!!!!!!');
}

function callCenterInit(data){
    link = this.href;
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: 'admin/call_center/callCenterInit',
        type: 'GET',
        dataType: 'html',
        data: data,
    })
        .done(function (data) {
            $(".content-call-center").html(data)
        })
        .fail(function () {
            console.log("error");
        });
}

function loadHtmlCallCenter(type = 1,csVoice = {}){
    var result = '';
    if (type == 1){
        if (csVoice.isCallout){
            result = `<div class="wrap_call">
                        <div class="wrap_call_agent">
                            <div class="bouton raccrocher" style="left: 30% !important;top: unset !important;bottom: 0">
                                <span class="icon red" onClick="endCall();"></span>
                            </div>
                        </div>
                    </div>`;
        } else {
            result = `<div class="wrap_call">
                        <div class="wrap_call_customer">
                            <div class="bouton raccrocher">
                                <span class="icon red" onClick="endCall();"></span>
                            </div>
                            <div class="bouton decrocher">
                                <span class="icon green" onClick="onAcceptCall();"></span>
                            </div>
                        </div>
                    </div>`;
        }
    } else if(type == 2){
        if (csVoice.isCallout) {
            $(".card-call-center").find('.phoneNumber').html(csVoice.callInfo.caller);
            $(".wrap_call").removeClass('hide');
            $(".wrap_calling").addClass('hide');
            result = `<div class="wrap_call">
                        <div class="wrap_call_agent">
                            <div class="bouton raccrocher" style="left: 30% !important;top: unset !important;bottom: 0">
                                <span class="icon red" onClick="endCall();"></span>
                            </div>
                        </div>
                    </div>`;
        } else {
            $(".card-call-center").find('.phoneNumber').html('Đang trả lời');
            result = `<div class="wrap_calling">
                        <div style="display: flex;margin-bottom: 25px;margin-top: 5px;margin-left: 10px">
                            <div class="call_mute">
                                <img class="icon-unmute" onClick="muteCall()" src="admin/assets/images/icon-unmute.png"
                                     style="width: 30px;cursor: pointer">
                                    <img class="icon-mute hide" onClick="muteCall()" src="admin/assets/images/icon-mute.png"
                                         style="width: 30px;cursor: pointer">
                            </div>
                            <div class="call_hold" style="margin-left: 15px">
                                <img class="icon-unhold " onClick="holdCall()" src="admin/assets/images/icon-unhold.svg"
                                     style="width: 30px;cursor: pointer">
                                    <img class="icon-hold hide" onClick="holdCall()" src="admin/assets/images/icon-hold.svg"
                                         style="width: 30px;cursor: pointer">
                            </div>
                        </div>
                        <div class="bouton raccrocher" style="left: 30% !important;top: unset !important;bottom: 0">
                            <span class="icon red" onClick="endCall();"></span>
                        </div>
                    </div>`;
            timeCall();
        }
    } else if(type == 3){
        $(".card-call-center").find('.phoneNumber').html('Đang trả lời');
        result = `<div class="wrap_calling">
                    <div style="display: flex;margin-bottom: 25px;margin-top: 5px;margin-left: 10px">
                        <div class="call_mute">
                            <img class="icon-unmute" onClick="muteCall()" src="admin/assets/images/icon-unmute.png"
                                 style="width: 30px;cursor: pointer">
                                <img class="icon-mute hide" onClick="muteCall()" src="admin/assets/images/icon-mute.png"
                                     style="width: 30px;cursor: pointer">
                        </div>
                        <div class="call_hold" style="margin-left: 15px">
                            <img class="icon-unhold " onClick="holdCall()" src="admin/assets/images/icon-unhold.svg"
                                 style="width: 30px;cursor: pointer">
                                <img class="icon-hold hide" onClick="holdCall()" src="admin/assets/images/icon-hold.svg"
                                     style="width: 30px;cursor: pointer">
                        </div>
                    </div>
                    <div class="bouton raccrocher" style="left: 30% !important;top: unset !important;bottom: 0">
                        <span class="icon red" onClick="endCall();"></span>
                    </div>
                </div>`;
        timeCall();
    }
    $(".card-call-center").find('.footer-call-center').html(result);
}

