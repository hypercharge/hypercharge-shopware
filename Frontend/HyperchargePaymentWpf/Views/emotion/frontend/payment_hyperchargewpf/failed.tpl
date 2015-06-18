{extends file='frontend/index/index.tpl'}
{* Breadcrumb *}
{block name='frontend_index_start' append}
    {include file="frontend/hypercharge/payment_hyperchargewpf/failed_index_start.tpl"}
{/block}

{* Main content *}
{block name='frontend_index_content'}
    {include file="frontend/hypercharge/payment_hyperchargewpf/failed.tpl"}
{/block}
