{if $sUserData.additional.payment.name == "hyperchargemobile_cc" || $sUserData.additional.payment.name == "hyperchargemobile_dd" || $sUserData.additional.payment.name == "hyperchargemobile_pa" || $sUserData.additional.payment.name == "hyperchargemobile_gd" || $sUserData.additional.payment.name == "hyperchargemobile_gp" || $sUserData.additional.payment.name == "hyperchargemobile_sd"}
    <form name="" method="POST" >
        <div class="payment_method">
            <h3 class="underline">
                {if $sUserData.additional.payment.name == "hyperchargemobile_cc"}
                    {s name=CreditCardData namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/confirm"}Credit Card Data{/s}
                {else if $sUserData.additional.payment.name == "hyperchargemobile_dd"}
                    {s name=BankData namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/confirm"}Bank Data{/s}
                {else if $sUserData.additional.payment.name == "hyperchargemobile_pa"}
                    {s name=ClientData namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/confirm"}Payolution{/s}
                {else if $sUserData.additional.payment.name == "hyperchargemobile_gd"}
                    {s name=BankDataSepaGTD namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/confirm"}GTD Sepa Debit Sale{/s}
                {else if $sUserData.additional.payment.name == "hyperchargemobile_gp"}
                    {s name=ClientDataGTD namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/confirm"}GTD Purchase On Account{/s}
                {else if $sUserData.additional.payment.name == "hyperchargemobile_sd"}
                    {s name=BankDataSepa namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/mobile/confirm"}Sepa Debit Sale{/s}
                {/if}
            </h3>
            <div class="grid_15 method">
                <input type="radio" name="register[payment]" class="radio auto_submit" value="{$sUserData.additional.payment.id}" id="payment_mean{$sUserData.additional.payment.id}" checked="checked" style="display: none;"/>
                <!-- div class="space">&nbsp;</div -->
                <div class="grid_10">
                    <div class="hyperchargedata" payment_id="{$sUserData.additional.payment.id}">
                        {include file="frontend/hypercharge/mobile/{$sUserData.additional.payment.name}.tpl"}
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