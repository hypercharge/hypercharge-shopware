{extends file="frontend/index/index.tpl"}
{block name='frontend_index_navigation'}{/block}
{block name='frontend_index_navigation_categories_top'}{/block}
{block name='frontend_index_search'}{/block}
{block name='frontend_index_breadcrumb'}{/block}
{block name="frontend_index_content_top"}{/block}
{block name='frontend_index_content_left'}{/block}
{block name='frontend_index_content'}
    {include file="frontend/hypercharge/payment_hyperchargewpf/return.tpl"}
{/block}
{block name='frontend_index_content_right'}{/block}
{block name="frontend_index_footer"}{/block}
{block name="frontend_index_shopware_footer"}{/block}
{block name='frontend_index_body_inline'}{/block}

{block name='frontend_index_header' append}
    {include file="frontend/hypercharge/payment_hyperchargewpf/return_header.tpl"}
{/block}

