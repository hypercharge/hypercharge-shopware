<!-- For new registering clients -->
{block name='frontend_checkout_payment_fieldset_template' append}
    {if $payment_mean.name == "hyperchargemobile_cc" || $payment_mean.name == "hyperchargemobile_dd" || $payment_mean.name == "hyperchargemobile_pa"}
        <div class="grid_10 hyperchargedata">
            {include file="frontend/payment_hyperchargewpf/{$payment_mean.name}.tpl"}
            <input type="hidden" value="{$nfxLang}" name="nfxLang"/>
        </div>
    {/if}
{/block}
<!-- For existing clients -->
{block name='frontend_checkout_confirm_payment' append}
    {if !$sRegisterFinished}
        {if $sUserData.additional.payment.name == "hyperchargemobile_cc" || $sUserData.additional.payment.name == "hyperchargemobile_dd" || $sUserData.additional.payment.name == "hyperchargemobile_pa"}
            <form name="" method="POST" >
                <div class="payment_method">
                    <h3 class="underline">
                        {if $sUserData.additional.payment.name == "hyperchargemobile_cc"}
                            {s name=CreditCardData namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}Credit Card Data{/s}
                        {else if $sUserData.additional.payment.name == "hyperchargemobile_dd"}
                            {s name=BankData namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}Bank Data{/s}
                        {else}
                            {s name=ClientData namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}Payolution{/s}
                        {/if}
                    </h3>
                    <div class="grid_15 method">
                        <input type="radio" name="register[payment]" class="radio auto_submit" value="{$sUserData.additional.payment.id}" id="payment_mean{$sUserData.additional.payment.id}" checked="checked" style="display: none;"/>
                        <!-- div class="space">&nbsp;</div -->
                        <div class="grid_10">
                            <div class="hyperchargedata">
                                {include file="frontend/payment_hyperchargewpf/{$sUserData.additional.payment.name}.tpl"}
                                <input type="hidden" value="{$nfxLang}" name="nfxLang"/>
                            </div>
                        </div>
                    </div>
                    <div class="clear">&nbsp;</div>
                </div>
            </form>
        {/if}
    {/if}
{/block}