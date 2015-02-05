<div class="hypercharge" id="hyperchargemobile-sepa_direct_debit">
    <p class="none">
        <label for="bank_account_holder">{s name=BankAccountHolder namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_gd"}Bank Account Holder*:{/s}</label>
        <input type="text" title="Bank Account Holder" class="text hyperchargemobile-sepa_direct_debit-field" id="bank_account_holder" value="" autocomplete="off" validation="required">
    </p>
    <p class="none">
        <label for="iban">{s name=IBAN namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_gd"}IBAN*:{/s}</label>
        <input type="text" title="IBAN" class="text hyperchargemobile-sepa_direct_debit-field" id="iban" value="" autocomplete="off" validation="required">
    </p>
    <p class="none">
        <label for="bic">{s name=BIC namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_gd"}BIC*:{/s}</label>
        <input type="text" title="BIC" class="text hyperchargemobile-sepa_direct_debit-field" id="bic" value="" autocomplete="off" validation="required">
    </p>
    <p class="none">
        <input type="text" id="sepa_mandate_id" class="hyperchargemobile-sepa_direct_debit-field" value="{$nfxSepaMandateId}" style="display:none"/>
        <input type="text" id="sepa_mandate_signature_date" class="hyperchargemobile-sepa_direct_debit-field" value="{$nfxSepaMandateSignatureDate}" style="display:none"/>
    </p>
    <p class="none">
            <label for="birthday_day">{s name=Birthday namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/hyperchargemobile_gd"}Date of Birth*:{/s}</label>
            <select id="birthday_day" class="day hyperchargemobile-sepa_direct_debit-field" autocomplete="off" validation="required">
                {for $day=1 to 31}
                    <option value="{$day}" {if $day == $nfxPayolutionBirthdayDay}selected{/if}>{$day}</option>
                {/for}
            </select>
            <select id="birthday_month" class="month hyperchargemobile-sepa_direct_debit-field" autocomplete="off" validation="required">
                {for $month=1 to 12}
                    <option value="{$month}" {if $month == $nfxPayolutionBirthdayMonth}selected{/if}>{$month}</option>
                {/for}
            </select>
            {assign var=thisyear value=$smarty.now|date_format:"%Y"}
            <select id="birthday_year" class="year hyperchargemobile-sepa_direct_debit-field" autocomplete="off" validation="required date {$nfxBirthdayValidation}">
                {for $year=$thisyear to 1900 step=-1}
                    <option value="{$year}" {if $year == $nfxPayolutionBirthdayYear}selected{/if}>{$year}</option>
                {/for}
            </select>
        </p>
    <input type="hidden" value="{$shopware_redirect}" id="hyperchargemobile_shopware_redirect"/>
    <input type="hidden" value="{$shopware_failed_redirect}" id="hyperchargemobile_shopware_failed_redirect"/>
</div>