<div class="block-center" id="">
    <h2>{l s='Pay by BazPardakht' mod='ibazpardakht'}</h2>

    {include file="$tpl_dir./errors.tpl"}

    <p>{l s='Your order on' mod='ibazpardakht'} <span class="bold">{$shop_name}</span> {l s='is not complete.' mod='ibazpardakht'}
        <br /><br /><span class="bold">{l s='There is some errors in your payment.' mod='ibazpardakht'}</span>
        <br /><br />{l s='For any questions or for further information, please contact our' mod='ibazpardakht'} <a href="{$link->getPageLink('contact-form', true)}">{l s='customer support' mod='ibazpardakht'}</a>.
    </p>

    {if !empty($res_num) || !empty($ref_num)}
        <p class="required">{l s='Payment Details' mod='ibazpardakht'}:</p>
        <p>
            {l s='Payment ID' mod='ibazpardakht'}: {$res_num}<br />
            {l s='Payment Reference:' mod='ibazpardakht'} {$ref_num}
        </p><br />
    {/if}

    <p style="float:left; font-size:9px;color:#c4c4c4">Bazpardakht ver <a href="http://upal.ir/" style="color:#c4c4c4">{$ver}</a></p>
</div>