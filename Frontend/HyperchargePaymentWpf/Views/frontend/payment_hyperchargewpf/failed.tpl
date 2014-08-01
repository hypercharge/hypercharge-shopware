{extends file='frontend/index/index.tpl'}

{* Breadcrumb *}
{block name='frontend_index_start' append}
    {$sBreadcrumb = [['name'=>"{s name=PaymentTitle namespace=HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/failed}Failed Payment{/s}"]]}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    <div id="center" class="grid_13">
        <div class="error center bold">
            <h2>{s name=PaymentProcessFailedTitle namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/failed"}Payment process failed{/s}</h2>
            {if $nfxErrorMessage}
                <p>{$nfxErrorMessage}</p>
            {else}
                <p>{s name=PaymentProcessFailedMessage namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/failed"}The payment process has failed. Please contact the store administrator for assistance.{/s}</p>
            {/if}
        </div>
    </div>
{/block}