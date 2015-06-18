{extends file="parent:frontend/checkout/change_payment.tpl"}
<!-- For new registering clients -->
{block name='frontend_checkout_payment_fieldset_template' append}
    {include file="frontend/hypercharge/mobile/change_payment.tpl"}
{/block}