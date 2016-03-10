
<div class="block-center" id="">
<h2>{l s='Pay by Upal' mod='upalpayment'}</h2>

{include file="$tpl_dir./errors.tpl"}

{if isset($prepay) && $prepay}
	<br />
	<p>{l s='Connecting to gateway' mod='upalpayment'}...</p>
	<p>{l s='If there is problem on redirectiong click on payment button bellow' mod='upalpayment'}</p>
	<script type="text/javascript">
		setTimeout("document.forms.frmpayment.submit();",10);
	</script>
	<form name="frmpayment" action="{$redirect_link}" method="post">
		<input type="hidden" id="id" name="id" value="{$ref_id}" />
		<input type="submit" class="button" value="{l s='Pay Now' mod='upalpayment'}" />
	</form>
	<p></p>
{/if}
</div>