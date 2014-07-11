(function($) {
    var nfxAPICall = 0;
    
    onSubmitForm = function(event) {
        var checked_method = $("input[name='register[payment]']:checked").parents('.method').find('.hyperchargedata').children('.hypercharge'),
                form = $("input[name='register[payment]']:checked").parents('form'),
                field, shopware_redirect_url, shopware_failed_redirect_url,
                json = '{ "payment": { ';

        if (checked_method.attr('id') == undefined) {
            //this is not a Hypercharge payment method
            return true;
        }
        //validate mandatory fields
        if(!form.validation()){
            return false;
        }
        //create JSON data
        field = '.' + checked_method.attr('id') + '-field';
        checked_method.find(field).each(function(index, item) {
            json += "\"" + item.id + "\" : \"" + $(item).val() + "\",";
        });
        json += "\"payment_method\" : \"" + checked_method.attr('id').replace("hyperchargemobile-", "") + "\" } }";

        var data = jQuery.parseJSON(json);

        //submit 1 to Hypercharge via Shopware
        shopware_redirect_url = checked_method.find('#hyperchargemobile_shopware_redirect').val();
        shopware_failed_redirect_url = checked_method.find('#hyperchargemobile_shopware_failed_redirect').val();
        sendFormToShopware(shopware_redirect_url, shopware_failed_redirect_url, data);

        return false;
    }

    function sendFormToShopware(formUrl, errorUrl, data) {
        //$('#confirm .actions input').attr("disabled", "disabled");//avoid sending the data more than once
        if(nfxAPICall == 1){
            return;
        }
        nfxAPICall = 1;
        jQuery.support.cors = true;
        jQuery.ajax({
            url: formUrl,
            type: 'POST',
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    sendFormToHypercharge(result.redirect_url, result.return_success_url, errorUrl, data);
                }
                return;
            },
            error: function(jqXHR, tranStatus, errorThrown) {
                //$('#confirm .actions input').attr("disabled", "");
                alert(errorThrown.message);
                nfxAPICall = 0;
                //window.location.href = errorUrl;
            }
        });
    }

    function sendFormToHypercharge(formUrl, successUrl, errorUrl, data) {
        jQuery.support.cors = true;
        jQuery.ajax({
            url: formUrl,
            type: 'POST',
            crossDomain: true,
            data: data,
            dataType: 'jsonp xml',
            success: function(result) {
                //$('#confirm .actions input').attr("disabled", "");
                window.location.href = successUrl;
                return true;
            },
            error: function(jqXHR, tranStatus, errorThrown) {
                //$('#confirm .actions input').attr("disabled", "");
                if (jqXHR.status == 200) {
                    //window.location.href = successUrl;
                    //we will not do the redirection to success action anymore because we need to submit the current form (for AGB)
                    $('#confirm .actions input').parents("form").submit();
                    return;
                } else {
                    alert(errorThrown.message);
                    window.location.href = errorUrl;
                    return;
                }
            }
        });
    }
    
    $(document).ready(function() {
        $('#confirm .actions input').bind('click', onSubmitForm);
    });
})(jQuery);
    
function toggleToolTip(value) {
    if (value) {
        jQuery("#payment-tool-tip").show();
    } else {
        jQuery("#payment-tool-tip").hide();
    }
    return false;
}