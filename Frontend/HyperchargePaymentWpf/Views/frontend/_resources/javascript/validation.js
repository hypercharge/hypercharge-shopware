(function($) {
    /*
     Validation Singleton
     */
    var Validation = function() {
        var rules = {
            required: {
                check: function(attr) {
                    if (attr["val"])
                        return true;
                    else
                        return false;
                },
                msg: 'required'
            },
            card_number: {
                check: function(attr) {
                    // remove non-numerics
                    var v = "0123456789";
                    var w = "";
                    var s = attr["val"];
                    for (i = 0; i < s.length; i++) {
                        x = s.charAt(i);
                        if (v.indexOf(x, 0) != -1)
                            w += x;
                    }
                    // validate number
                    j = w.length / 2;
                    k = Math.floor(j);
                    m = Math.ceil(j) - k;
                    c = 0;
                    for (i = 0; i < k; i++) {
                        a = w.charAt(i * 2 + m) * 2;
                        c += a > 9 ? Math.floor(a / 10 + a % 10) : a;
                    }
                    for (i = 0; i < k + m; i++)
                        c += w.charAt(i * 2 + 1 - m) * 1;
                    return (c % 10 == 0);
                },
                msg: "card_number"
            },
            card_type: {
                check: function(attr) {
                    if (!attr["val"] || !attr["card_number"])
                        return true;

                    var v = removeDelimeters(attr["card_number"]);
                    if (creditCardTypes[attr["val"]][0] == false) {
                        return true;
                    }

                    var re = creditCardTypes[attr["val"]][0];

                    if (v.match(re)) {
                        return true;
                    }

                    return false;
                },
                msg: "card_type"
            },
            cvv: {
                check: function(attr) {
                    if (!attr["val"] || !attr["card_type"])
                        return true;
                    
                    var re = creditCardTypes[attr["card_type"]][1];
                    var v = attr["val"];

                    if (v.match(re)) {
                        return true;
                    }

                    return false;
                },
                msg: "cvv"
            },
            expiration_date: {
                check: function(attr) {
                    if (!attr["val"] || !attr["expiration_month"])
                        return true;

                    var ccExpMonth = attr["expiration_month"];
                    var ccExpYear = attr["val"];
                    var currentTime = new Date();
                    var currentMonth = currentTime.getMonth() + 1;
                    var currentYear = currentTime.getFullYear();
                    if (ccExpMonth < currentMonth && ccExpYear == currentYear) {
                        return false;
                    }
                    return true;
                },
                msg: 'expiration_date'
            }
        }
        var testPattern = function(value, pattern) {

            var regExp = new RegExp(pattern, "");
            return regExp.test(value);
        }
        var creditCardTypes = {
            //    'SS': [new RegExp('^((6759[0-9]{12})|(5018|5020|5038|6304|6759|6761|6763[0-9]{12,19})|(49[013][1356][0-9]{12})|(6333[0-9]{12})|(6334[0-4]\d{11})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
            'SO': [new RegExp('^(6334[5-9]([0-9]{11}|[0-9]{13,14}))|(6767([0-9]{12}|[0-9]{14,15}))$'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
            'SM': [new RegExp('(^(5[0678])[0-9]{11,18}$)|(^(6[^05])[0-9]{11,18}$)|(^(601)[^1][0-9]{9,16}$)|(^(6011)[0-9]{9,11}$)|(^(6011)[0-9]{13,16}$)|(^(65)[0-9]{11,13}$)|(^(65)[0-9]{15,18}$)|(^(49030)[2-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49033)[5-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49110)[1-2]([0-9]{10}$|[0-9]{12,13}$))|(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))|(^(4936)([0-9]{12}$|[0-9]{14,15}$))'), new RegExp('^([0-9]{3}|[0-9]{4})?$'), true],
            'VI': [new RegExp('^4[0-9]{12}([0-9]{3})?$'), new RegExp('^[0-9]{3}$'), true],
            'MC': [new RegExp('^5[1-5][0-9]{14}$'), new RegExp('^[0-9]{3}$'), true],
            'AE': [new RegExp('^3[47][0-9]{13}$'), new RegExp('^[0-9]{4}$'), true],
            'DI': [new RegExp('^6011[0-9]{12}$'), new RegExp('^[0-9]{3}$'), true],
            'JCB': [new RegExp('^(3[0-9]{15}|(2131|1800)[0-9]{11})$'), new RegExp('^[0-9]{3,4}$'), true],
            'OT': [false, new RegExp('^([0-9]{3}|[0-9]{4})?$'), false]
        };
        return {
            addRule: function(name, rule) {

                rules[name] = rule;
            },
            getRule: function(name) {

                return rules[name];
            }
        }
    }

    /* 
     Form factory 
     */
    var Form = function(form) {
        var fields = [];
        //form.find(".err_mess_text").html('');
        //form.find(".err_mess_box").css("display","none");
        form.find("[validation]").each(function() {
            var field = $(this);
            //validate only the fields specific for the selected hypercharge method
            if (field.parents('.hypercharge').parents('.hyperchargedata').parents('.method').find("input[name='register[payment]']:checked").attr('name') != undefined) {
                if (field.attr('validation') !== undefined) {
                    fields.push(new Field(field));
                }
            }
        });
        this.fields = fields;
    }
    Form.prototype = {
        validate: function() {

            for (field in this.fields) {
                this.fields[field].validate();
            }
        },
        isValid: function() {
            for (field in this.fields) {

                if (!this.fields[field].valid) {

                    this.fields[field].field.focus();
                    return false;
                }
            }
            return true;
        }
    }

    /* 
     Field factory 
     */
    var Field = function(field) {
        this.field = field;
        this.valid = false;
        //this.attach("change");
    }
    Field.prototype = {
        attach: function(event) {

            var obj = this;
            if (event == "change") {
                obj.field.bind("change", function() {
                    return obj.validate();
                });
            }
            if (event == "keyup") {
                obj.field.bind("keyup", function(e) {
                    return obj.validate();
                });
            }
        },
        validate: function() {
            var obj = this,
                    field = obj.field,
                    errorClass = "errorlist",
                    errorlist = $(document.createElement("ul")).addClass(errorClass),
                    types = field.attr("validation").split(" "),
                    container = field.parent(),
                    errors = [],
                    messages = {
                        'required' : {
                            'en' : "This is a required field.",
                            'de' : 'Diese Angabe wird benötigt.'
                        },
                        'card_number' : {
                            'en' : "Please enter a valid credit card number.",
                            'de' : 'Bitte geben Sie eine gültige Kreditkartennummer an.'
                        },
                        'card_type' : {
                            'en' : "Card type does not match credit card number.",
                            'de' : "Kartentyp stimmt nicht mit Kreditkartennummer überein."
                        },
                        'cvv' : {
                            'en' : "Please enter a valid credit card verification number.",
                            'de' : "Bitte geben Sie eine gültige Kreditkarten Kontroll-Nummer/Verifizierungscode ein."
                        },
                        'expiration_date' : {
                            'en' : "Incorrect credit card expiration date.",
                            'de' : 'Falsches Kreditkarten - Ablaufdatum.'
                        }
                    };
            
            if (container.prop('tagName') != "P") {
                container = container.parent();
            }
            //field.next(".errorlist").remove();
            container.children(".errorlist").remove();
            for (var type in types) {
                var rule = $.Validation.getRule(types[type]);
                var attr = [];
                attr["val"] = field.val();
                if (types[type] == "card_type") {
                    attr["card_number"] = field.parents('.hypercharge').find("#card_number").val();
                }
                if (types[type] == "expiration_date") {
                    attr["expiration_month"] = field.parents('.hypercharge').find("#expiration_month").val();
                }
                if (types[type] == "cvv") {
                    attr["card_type"] = field.parents('.hypercharge').find("#card_type").val();
                }
                //attr["disabled"] = field.attr("disabled");
                //attr["required_group_ok"] = field.attr("required_group_ok");
                //attr["interval_ok"] = field.attr("interval_ok");
                if (!rule.check(attr)) {
                    //field.addClass("errorbox");
                    //container.addClass("error");
                    var lang = field.parents('.hypercharge').parents('.hyperchargedata').find("input[name='nfxLang']").val(),
                        msg = eval("messages[rule.msg]." + lang);
                
                    if(msg == undefined){
                        msg = messages[rule.msg].de;
                    }
                    
                    errors.push(msg);
                }
            }

            if (errors.length) {

                obj.field.unbind("keyup")
                obj.attach("keyup");
                //field.after(errorlist.empty()); //-- asta afiseaza mesajul imediat dupa camp
                container.append(errorlist.empty());
                for (error in errors) {

                    errorlist.append("<li>" + errors[error] + "</li>");

                    //-- asta ca sa se afiseze toate mesajele intr-un div
                    /*b = true;
                     field.parents("form").find(".err_mess_text").find("li").each(function() {
                     if ($(this).html() == errors[error])
                     b = false;
                     });
                     if (b)
                     field.parents("form").find(".err_mess_text").append("<li>" + errors[error] + "</li>");
                     */

                }

                //field.parents("form").find(".err_mess_box").css("display", "block");

                obj.valid = false;
            }
            else {
                errorlist.remove();
                //container.removeClass("error");
                //field.removeClass("errorbox");
                obj.valid = true;
            }
        }
    }

    /*
     Validation extends jQuery prototype
     */
    $.extend($.fn, {
        validation: function() {
            var validator = new Form($(this));
            $.data($(this)[0], 'validator', validator);

            /*$(this).bind("submit", function(e) {
             validator.validate();
             if(!validator.isValid()) {
             e.preventDefault();
             }
             });*/
            validator.validate();
            return validator.isValid();
        },
        validate: function() {
            if ($(this)[0] == undefined) {
                return true;
            }

            var validator = $.data($(this)[0], 'validator');

            if (validator == undefined) {
                return true;
            }

            validator.validate();

            return validator.isValid();

        }
    });
    $.Validation = new Validation();

})(jQuery);


function removeDelimeters(v) {
    v = v.replace(/\s/g, '');
    v = v.replace(/\-/g, '');
    return v;
}