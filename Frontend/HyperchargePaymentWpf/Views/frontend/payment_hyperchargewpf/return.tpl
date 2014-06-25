{extends file='frontend/index/index.tpl'}
{block name='frontend_index_navigation'}{/block}
{block name='frontend_index_navigation_categories_top'}{/block}
{block name='frontend_index_search'}{/block}
{block name='frontend_index_breadcrumb'}{/block}
{block name="frontend_index_content_top"}{/block}
{block name='frontend_index_content_left'}{/block}
{block name='frontend_index_content'}
    <div style="height:{$nfxHeight}px !important; border: 0 none;">
        <div id="payment_loader" class="ajaxSlider" style="height: 100px; border: 0 none;">
            <div class="loader">{s name="PaymentInfoWait" namespace="HyperchargePaymentWpf/Views/frontend/payment_hyperchargewpf/return"}Bitte warten...{/s}</div>
        </div>
    </div>
{/block}
{block name='frontend_index_content_right'}{/block}
{block name="frontend_index_footer"}{/block}
{block name="frontend_index_shopware_footer"}{/block}
{block name='frontend_index_body_inline'}{/block}

{block name='frontend_index_header' append}
<script type="text/javascript">
//<![CDATA[
    var url = '{$nfxRedirectURL}';
    url = decodeURIComponent(url.replace(/\+/g, ' '));
    top.location=decodeURI(url);

//]]>
</script>
{/block}
