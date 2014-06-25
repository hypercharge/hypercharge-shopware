<div class="hypercharge" id="hyperchargemobile-credit_card">
    <p class="none">
        <label for="card_holder">{s name=NameOnCard namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Name on Card*:{/s}</label>
        <input type="text" title="Name on Card" class="text hyperchargemobile-credit_card-field" id="card_holder" value="" autocomplete="off" validation="required">
    </p>
    <p class="none">
        <label for="card_type">{s name=CreditCardType namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Credit Card Type*:{/s}</label>
        <select id="card_type" autocomplete="off" validation="required card_type">
            <option value="">{s name=CreditCardTypePleaseSelect namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}--Please Select--{/s}</option>
            {foreach from=$credit_card_types item=type}
                <option value="{$type[0]}">{$type[1]}</option>
            {/foreach}
        </select>
    </p>
    <p class="none">
        <label for="card_number">{s name=CreditCardNumber namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Credit Card Number*:{/s}</label>
        <input type="text" title="Credit Card Number" class="text hyperchargemobile-credit_card-field" id="card_number" value="" autocomplete="off" validation="required card_number">
    </p>
    <p class="none">
        <label for="expiration_month">{s name=ExpirationDate namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Expiration Date*:{/s}</label>
        <select id="expiration_month" class="month hyperchargemobile-credit_card-field" autocomplete="off" validation="required">
            <option value="" selected="selected">{s name=Month namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Month{/s}</option>
            <option value="1">{s name=January namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}01 - January{/s}</option>
            <option value="2">{s name=February namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}02 - February{/s}</option>
            <option value="3">{s name=March namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}03 - March{/s}</option>
            <option value="4">{s name=April namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}04 - April{/s}</option>
            <option value="5">{s name=May namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}05 - May{/s}</option>
            <option value="6">{s name=June namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}06 - June{/s}</option>
            <option value="7">{s name=July namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}07 - July{/s}</option>
            <option value="8">{s name=August namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}08 - August{/s}</option>
            <option value="9">{s name=September namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}09 - September{/s}</option>
            <option value="10">{s name=October namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}10 - October{/s}</option>
            <option value="11">{s name=November namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}11 - November{/s}</option>
            <option value="12">{s name=December namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}12 - December{/s}</option>
        </select>
        {assign var=thisyear value=$smarty.now|date_format:"%Y"}
        <select id="expiration_year" class="year hyperchargemobile-credit_card-field" autocomplete="off" validation="required expiration_date">
            <option value="" selected="selected">{s name=Year namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Year{/s}</option>
            {for $month=$thisyear to $thisyear+10}
                <option value="{$month}">{$month}</option>
            {/for}
        </select>
    </p>
    <p class="none">
        <label for="cvv">{s name=CardVerificationNumber namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}Card Verification Number*:{/s}</label>
        <input type="text" title="Card Verification Number" class="text hyperchargemobile-credit_card-field" id="cvv" value="" autocomplete="off" validation="required cvv">
        <a href="#" class="cvv-what-is-this" onclick="javascript:toggleToolTip(true);
                    return false;">{s name=WhatIsThis namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_cc"}What is this?{/s}</a>
    </p>
    <div class="tool-tip" id="payment-tool-tip" style="display:none">
        <div class="btn-close"><a href="#" id="payment-tool-tip-close" title="Close" onclick="javascript:toggleToolTip(false);
                    return false;">Close</a></div>
        <div class="tool-tip-content"><img src="{link file='frontend/_resources/images/cvv.gif'}" alt="Card Verification Number Visual Reference" title="Card Verification Number Visual Reference"></div>
    </div>
    <input type="hidden" value="{$shopware_redirect}" id="hyperchargemobile_shopware_redirect"/>
    <input type="hidden" value="{$shopware_failed_redirect}" id="hyperchargemobile_shopware_failed_redirect"/>
</div>