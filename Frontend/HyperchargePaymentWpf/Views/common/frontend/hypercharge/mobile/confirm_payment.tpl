{if $payment_mean.name == "hyperchargemobile_cc" || $payment_mean.name == "hyperchargemobile_dd" || $payment_mean.name == "hyperchargemobile_pa" || $payment_mean.name == "hyperchargemobile_gd" || $payment_mean.name == "hyperchargemobile_gp"}
    <div class="grid_10 hyperchargedata" payment_id="{$payment_mean.id}">
        {include file="frontend/hypercharge/mobile/{$payment_mean.name}.tpl"}
        <input type="hidden" value="{$nfxLang}" name="nfxLang"/>
        <input type="hidden" value="{$nfxAGBMsg}" name="nfxAGBMsg"/>
    </div>
{elseif $payment_mean.name|substr:0:14 == "hyperchargewpf"}
    <div class="grid_10 hyperchargedata" payment_id="{$payment_mean.id}">
        <div class="hypercharge" id="{$payment_mean.name}"></div>
    </div>
{/if}