<!-- For new registering clients -->
{block name='frontend_checkout_payment_fieldset_template' append}
    {include file="frontend/hypercharge/mobile/confirm_payment.tpl"}
{/block}
<!-- For existing clients -->
{block name='frontend_checkout_confirm_payment' append}
    {* if !$sRegisterFinished *}
        {include file="frontend/hypercharge/mobile/confirm.tpl"}
    {*/if *}
{/block}