{extends file="parent:frontend/checkout/confirm.tpl"}
<!-- For existing clients -->
{block name='frontend_checkout_confirm_product_table' prepend}
    {include file="frontend/hypercharge/mobile/confirm.tpl"}
{/block}