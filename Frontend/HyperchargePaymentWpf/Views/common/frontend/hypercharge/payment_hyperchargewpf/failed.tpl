{if $nfxFailedAction}
    <div class="nfx-grid">
        <div class="error center bold">
            <h2 style="background-color:transparent !important;">{s name=PaymentTitle namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/payment_hyperchargewpf/failed"}Payment process failed{/s}</h2>
            {if $nfxErrorMessage}
                <p>{$nfxErrorMessage}</p>
            {else}
                <p>{s name=PaymentProcessFailedMessage namespace="HyperchargePaymentWpf/Views/common/frontend/hypercharge/payment_hyperchargewpf/failed"}The payment process has failed. Please contact the store administrator for assistance.{/s}</p>
            {/if}
        </div>
    </div>
{/if}