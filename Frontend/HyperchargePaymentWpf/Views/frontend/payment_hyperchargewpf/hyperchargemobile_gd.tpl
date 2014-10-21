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
    <input type="hidden" value="{$shopware_redirect}" id="hyperchargemobile_shopware_redirect"/>
    <input type="hidden" value="{$shopware_failed_redirect}" id="hyperchargemobile_shopware_failed_redirect"/>
</div>