document.ready = function (callback) {
    ///兼容FF,Google
    if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', function () {
            document.removeEventListener('DOMContentLoaded', arguments.callee, false);
            callback();
        }, false)
    }
    //兼容IE
    else if (document.attachEvent) {
        document.attachEvent('onreadystatechange', function () {
            if (document.readyState == "complete") {
                document.detachEvent("onreadystatechange", arguments.callee);
                callback();
            }
        })
    }
    else if (document.lastChild == document.body) {
        callback();
    }
}

function onSubmit(token) {
    jQuery("form")[0].submit();
}

document.ready(function () {
    var submit_div = jQuery(".submit>button")[0];
    jQuery(submit_div).addClass("g-recaptcha");
    jQuery(submit_div).attr("data-sitekey", GoogleRepactSiteKey);
    jQuery(submit_div).attr("data-callback", "onSubmit");
});
