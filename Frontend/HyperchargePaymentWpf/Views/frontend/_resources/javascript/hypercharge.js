(function($) {
    var nfxAPICall = 0;

    onSubmitForm = function(event) {
        var checked_method = $("input[name='register[payment]']:checked").parents('.method').find('.hyperchargedata').children('.hypercharge'),
                form = $("input[name='register[payment]']:checked").parents('form'),
                field, shopware_redirect_url, shopware_failed_redirect_url,
                agb = $("input:checkbox[id='sAGB']"),
                json = '{ "payment": { ',
                json2 = '{ "payolution": { ',
                birthday = [], //extra check for Purchase On Account
                shipping_address = [];//extra check for Purchase On Account

        if (checked_method.attr('id') == undefined) {
            //this is not a Hypercharge payment method
            return true;
        }
        if (checked_method.attr('id').substring(0, 14) == "hyperchargewpf") {
            //WPF
            if (agb.attr('id') != undefined) {
                //if AGB is not check we let SHopware validation
                if (agb.prop('checked') == false) {
                    return true;
                }
                if (nfxAPICall == 1) {
                    return false;//avoid double click
                }
                nfxAPICall = 1;
            }

            return true;
        }
        //Mobile
        //validate mandatory fields
        if (!form.validation()) {
            return false;
        }
        if (agb.attr('id') != undefined) {
            //we want to validate AGB before sending data to Hypercharge
            if (agb.prop('checked') == false) {
                alert(checked_method.parents('.hyperchargedata').find("input[name='nfxAGBMsg']").val());
                return false;
            }
        }
        //create JSON data
        field = '.' + checked_method.attr('id') + '-field';
        checked_method.find(field).each(function(index, item) {
            if (item.id.substring(0, 8) == "birthday") {//extra check for Purchase On Account
                birthday[item.id.replace("birthday_", "")] = $(item).val();
                json2 += "\"" + item.id + "\" : \"" + $(item).val() + "\",";
            } else if (item.id.substring(0, 16) == "shipping_address") {//extra check for Purchase On Account
                shipping_address[item.id.replace("shipping_address_", "")] = $(item).val();
            } else {
                json += "\"" + item.id + "\" : \"" + $(item).val() + "\",";
            }
        });
        if (shipping_address["first_name"] != undefined) {
            //Purchase On Account
            json += '"shipping_address": { ';
            json += '"first_name":"' + shipping_address["first_name"] + '",';
            json += '"last_name":"' + shipping_address["last_name"] + '",';
            json += '"address1":"' + shipping_address["address1"] + '",';
            json += '"zip_code":"' + shipping_address["zip_code"] + '",';
            json += '"city":"' + shipping_address["city"] + '",';
            if (shipping_address["state"] != undefined) {
                json += '"state":"' + shipping_address["state"] + '",';
            }
            json += '"country":"' + shipping_address["country"] + '"';
            json += '}, ';
        }
        if (birthday['year'] != undefined && birthday['month'] != undefined && birthday['day'] != undefined) {
            //Purchase On Account
            birthday['month'] = ('00' + birthday['month']).slice(-2);
            birthday['day'] = ('00' + birthday['day']).slice(-2);
            json += "\"risk_params\":{\"birthday\":\"" + birthday['year'] + "-" + birthday['month'] + "-" + birthday['day'] + "\"},";
            var agree = checked_method.find("#agree");
            if (agree.attr('id') != undefined) {
                json2 += "\"" + agree.attr('id') + "\" : \"" + $(agree).val() + "\"";
            }
        }
        json += "\"payment_method\" : \"" + checked_method.attr('id').replace("hyperchargemobile-", "") + "\" } }";
        json2 += " } }";

        var data = jQuery.parseJSON(json);
        var data2 = jQuery.parseJSON(json2);

        //submit 1 to Hypercharge via Shopware
        shopware_redirect_url = checked_method.find('#hyperchargemobile_shopware_redirect').val();
        shopware_failed_redirect_url = checked_method.find('#hyperchargemobile_shopware_failed_redirect').val();
        sendFormToShopware(shopware_redirect_url, shopware_failed_redirect_url, data, data2);

        return false;
    }

    function sendFormToShopware(formUrl, errorUrl, data, data2) {
        //$('#confirm .actions input').attr("disabled", "disabled");//avoid sending the data more than once
        if (nfxAPICall == 1) {
            return;
        }
        nfxAPICall = 1;
        jQuery.support.cors = true;
        jQuery.ajax({
            url: formUrl,
            type: 'POST',
            dataType: 'json',
            data: data2,
            success: function(result) {
                if (result.success) {
                    sendFormToHypercharge(result.redirect_url, result.return_success_url, errorUrl, data);
                } else {
                    window.location.href = errorUrl;
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
            dataType: "xml",
            headers: { 
                'origin': this.headerOrigin 
            },
            success: function(result) {
                //$('#confirm .actions input').attr("disabled", "");
                try{
                    var status = $(result).find("status").text();
                    /*var msg = $(result).find("message").text();
                    if($(result).find("message").text() != $(result).find("technical_message").text()){
                        msg += $(result).find("technical_message").text();
                    }*/
                    if((status == 'approved') || (status == 'pending') || (status == 'pending_async')){
                        window.location.href = successUrl;
                    } else {
                        window.location.href = errorUrl;
                    }
                }catch(e){
                    window.location.href = errorUrl;
                }
                return true;
            },
            error: function(jqXHR, tranStatus, errorThrown) {
                //$('#confirm .actions input').attr("disabled", "");
                if (jqXHR.status == 200) {
                    //window.location.href = successUrl;
                    ////we will not do the redirection to success action anymore because we need to submit the current form (for AGB)
                    //$('#confirm .actions input').parents("form").submit();//this is not valid anymore
                    window.location.href = errorUrl;
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

        var payments = $("input[name='register[payment]']");
        for (var i = 0; i < payments.length; i++) {
            var pay = payments[i];
            if ($(pay).parents('.method').find('.hyperchargedata').children('.hypercharge').attr('id') == 'hyperchargemobile-purchase_on_account') {
                if ($(pay).parents('.method').find('.hyperchargedata').children('.hypercharge').find("#not_allowed").attr("id") != undefined) {
                    if ($(pay).parents('.method').find('.hyperchargedata').children('.hypercharge').find("#not_allowed").val() == 1) {
                        $(pay).attr("disabled", "disabled");
                    } else {
                        $(pay).attr("disabled", "");
                    }
                }
            }
        }
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