<div class="hypercharge" id="hyperchargemobile-direct_debit">
    {if !$nfxHideFields}
        <p class="none">
            <label for="bank_account_holder">{s name=BankAccountHolder namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/hyperchargemobile_dd"}Bank Account Holder*:{/s}</label>
            <input type="text" title="Bank Account Holder" class="text hyperchargemobile-direct_debit-field" id="bank_account_holder" value="" autocomplete="off" validation="required">
        </p>
        <p class="none">
            <label for="bank_account_number">{s name=BankAccountNumber namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/hyperchargemobile_dd"}Bank Account Number*:{/s}</label>
            <input type="text" title="Bank Account Number" class="text hyperchargemobile-direct_debit-field" id="bank_account_number" value="" autocomplete="off" validation="required">
        </p>
        <p class="none">
            <label for="bank_number">{s name=BankNumber namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/hyperchargemobile_dd"}Bank Number*:{/s}</label>
            <input type="text" title="Bank Number" class="text hyperchargemobile-direct_debit-field" id="bank_number" value="" autocomplete="off" validation="required">
        </p>
        <input type="hidden" value="{$shopware_redirect}" id="hyperchargemobile_shopware_redirect"/>
        <input type="hidden" value="{$shopware_failed_redirect}" id="hyperchargemobile_shopware_failed_redirect"/>
    {/if}
</div>