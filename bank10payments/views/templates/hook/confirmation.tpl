{l s='Your order on %s is complete.' sprintf=$shop_name mod='upalpayment'}
		{if !isset($reference)}
			<br /><br />{l s='Your order number' mod='upalpayment'}: {$id_order}
		{else}
			<br /><br />{l s='Your order number' mod='upalpayment'}: {$id_order}
			<br /><br />{l s='Your order reference' mod='upalpayment'}: {$reference}
		{/if}		<br /><br />{l s='An email has been sent with this information.' mod='upalpayment'}
		<br /><br /> <strong>{l s='Your order will be sent as soon as posible.' mod='upalpayment'}</strong>
		<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='upalpayment'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='upalpayment'}</a>.
	</p><br />