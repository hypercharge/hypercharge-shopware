{extends file='parent:frontend/index/index.tpl'}
{block name='frontend_index_content_left'}{/block}

{* Breadcrumb *}
{block name='frontend_index_start' append}
	{include file="frontend/hypercharge/payment_hyperchargewpf/index_start.tpl"}
{/block}

{block name="frontend_index_content"}
    {include file="frontend/hypercharge/payment_hyperchargewpf/index.tpl"}
{/block}


