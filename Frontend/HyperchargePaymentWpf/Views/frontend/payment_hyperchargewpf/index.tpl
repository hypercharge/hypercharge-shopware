{extends file='frontend/index/index.tpl'}

{block name='frontend_index_content_left'}{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{$sBreadcrumb = [['name'=>"{s name=PaymentTitle namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/index"}Zahlung mit Hypercharge{/s}"]]}
{/block}

{block name="frontend_index_content"}
<div id="payment" class="grid_sagepay" style="margin:0 auto;width:{$nfxWidth}px;height:{$nfxHeight}px !important; border: 0 none;">
<iframe id="paymentFrame" src="{$nfxHyperchargeGatewayUrl}"
        scrolling="yes"
        frameborder="0"
        style="min-height:{$nfxHeight}px; height:auto !important;"
>
</iframe>
</div>
{/block}

