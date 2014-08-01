<div class="hypercharge" id="hyperchargemobile-purchase_on_account">

    {if !$nfxAllowedCountry}
        <p class="none">
        <ul class="errorlist">
            <li>
                {s name=NotAllowedCountryError namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_pa"}This payment method is not allowed for this country.{/s}
            </li>
        </ul>
        <input type="hidden" id="not_allowed" value="1" validation="not_allowed">
        </p>
    {else if !$nfxSameAddress}
        <p class="none">
        <ul class="errorlist">
            <li>
                {s name=NotSameAddressesError namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_pa"}For this payment method the shipping address must be the same as billing address.{/s}
            </li>
        </ul>
        <input type="hidden" id="not_allowed" value="1" validation="not_allowed">
        </p>
    {else}
        <p class="none">
            <label for="birthday_day">{s name=Birthday namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_pa"}Date of Birth*:{/s}</label>
            <select id="birthday_day" class="day hyperchargemobile-purchase_on_account-field" autocomplete="off" validation="required">
                {for $day=1 to 31}
                    <option value="{$day}" {if $day == $nfxPayolutionBirthdayDay}selected{/if}>{$day}</option>
                {/for}
            </select>
            <select id="birthday_month" class="month hyperchargemobile-purchase_on_account-field" autocomplete="off" validation="required">
                {for $month=1 to 12}
                    <option value="{$month}" {if $month == $nfxPayolutionBirthdayMonth}selected{/if}>{$month}</option>
                {/for}
            </select>
            {assign var=thisyear value=$smarty.now|date_format:"%Y"}
            <select id="birthday_year" class="year hyperchargemobile-purchase_on_account-field" autocomplete="off" validation="required birthday date">
                {for $year=$thisyear to 1900 step=-1}
                    <option value="{$year}" {if $year == $nfxPayolutionBirthdayYear}selected{/if}>{$year}</option>
                {/for}
            </select>
        </p>
        <p class="none">
            <input type="text" id="shipping_address_first_name" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["shippingaddress"]["firstname"]}" style="display:none"/>
            <input type="text" id="shipping_address_last_name" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["shippingaddress"]["lastname"]}" style="display:none"/>
            <input type="text" id="shipping_address_address1" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["shippingaddress"]["street"]} {$sUserData["shippingaddress"]["streetnumber"]}" style="display:none"/>
            <input type="text" id="shipping_address_city" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["shippingaddress"]["city"]}" style="display:none"/>
            <input type="text" id="shipping_address_zip_code" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["shippingaddress"]["zipcode"]}" style="display:none"/>
            <input type="text" id="shipping_address_country" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["additional"]["countryShipping"]["countryiso"]}" style="display:none"/>
            <input type="text" id="shipping_address_state" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData["additional"]["stateShipping"]["shortcode"]}" style="display:none"/>
            {if $sUserData['shippingaddress']["company"]}
                <input type="text" id="company_name" class="hyperchargemobile-purchase_on_account-field" value="{$sUserData['shippingaddress']["company"]}" style="display:none"/>
            {/if}
        </p>
        <p class="none">
            <input type="checkbox" id="agree" class="left" {if $nfxPayolutionAgree == "on"}checked{/if}>
            <label for="agree" class="chklabel" style="width:350px;">{$nfxAgreeText}</label>
        </p>
        <p class="none">
            <input type="text" style="display:none" validation="agree"/>
        </p>
    {/if}
    <input type="hidden" value="{$shopware_redirect}" id="hyperchargemobile_shopware_redirect"/>
    <input type="hidden" value="{$shopware_failed_redirect}" id="hyperchargemobile_shopware_failed_redirect"/>
</div>