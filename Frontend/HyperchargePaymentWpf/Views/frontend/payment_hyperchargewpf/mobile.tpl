<!-- For new registering clients -->
{block name='frontend_checkout_payment_fieldset_template' append}
    {if $payment_mean.name == "hyperchargemobile_cc" || $payment_mean.name == "hyperchargemobile_dd" || $payment_mean.name == "hyperchargemobile_pa" || $payment_mean.name == "hyperchargemobile_gd" || $payment_mean.name == "hyperchargemobile_gp"}
        <div class="grid_10 hyperchargedata" payment_id="{$payment_mean.id}">
            {include file="frontend/payment_hyperchargewpf/{$payment_mean.name}.tpl"}
            <input type="hidden" value="{$nfxLang}" name="nfxLang"/>
            <input type="hidden" value="{$nfxAGBMsg}" name="nfxAGBMsg"/>
        </div>
    {elseif $payment_mean.name|substr:0:14 == "hyperchargewpf"}
        <div class="grid_10 hyperchargedata" payment_id="{$payment_mean.id}">
            <div class="hypercharge" id="{$payment_mean.name}"></div>
        </div>
    {/if}
{/block}
<!-- For existing clients -->
{block name='frontend_checkout_confirm_payment' append}
    {if !$sRegisterFinished}
        {if $sUserData.additional.payment.name == "hyperchargemobile_cc" || $sUserData.additional.payment.name == "hyperchargemobile_dd" || $sUserData.additional.payment.name == "hyperchargemobile_pa" || $sUserData.additional.payment.name == "hyperchargemobile_gd" || $sUserData.additional.payment.name == "hyperchargemobile_gp"}
            <form name="" method="POST" >
                <div class="payment_method">
                    <h3 class="underline">
                        {if $sUserData.additional.payment.name == "hyperchargemobile_cc"}
                            {s name=CreditCardData namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}Credit Card Data{/s}
                        {else if $sUserData.additional.payment.name == "hyperchargemobile_dd"}
                            {s name=BankData namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}Bank Data{/s}
                        {else if $sUserData.additional.payment.name == "hyperchargemobile_pa"}
                            {s name=ClientData namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}Payolution{/s}
                        {else if $sUserData.additional.payment.name == "hyperchargemobile_gd"}
                            {s name=BankDataSepa namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}GTD Sepa Debit Sale{/s}
                        {else if $sUserData.additional.payment.name == "hyperchargemobile_gp"}
                            {s name=ClientDataGTD namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/mobile"}GTD Purchase On Account{/s}
                        {/if}
                    </h3>
                    <div class="grid_15 method">
                        <input type="radio" name="register[payment]" class="radio auto_submit" value="{$sUserData.additional.payment.id}" id="payment_mean{$sUserData.additional.payment.id}" checked="checked" style="display: none;"/>
                        <!-- div class="space">&nbsp;</div -->
                        <div class="grid_10">
                            <div class="hyperchargedata" payment_id="{$sUserData.additional.payment.id}">
                                {include file="frontend/payment_hyperchargewpf/{$sUserData.additional.payment.name}.tpl"}
                                <input type="hidden" value="{$nfxLang}" name="nfxLang"/>
                                <input type="hidden" value="{$nfxAGBMsg}" name="nfxAGBMsg"/>
                            </div>
                        </div>
                    </div>
                    <div class="clear">&nbsp;</div>
                </div>
            </form>
        {elseif $sUserData.additional.payment.name|substr:0:14 == "hyperchargewpf"}
            <div class="grid_15 method">
                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$sUserData.additional.payment.id}" id="payment_mean{$sUserData.additional.payment.id}" checked="checked" style="display: none;"/>
                <div class="grid_10">
                    <div class="hyperchargedata" payment_id="{$sUserData.additional.payment.id}">
                        <div class="hypercharge" id="{$sUserData.additional.payment.name}"></div>
                    </div>
                </div>
            </div>
        {/if}
    {/if}
{/block}