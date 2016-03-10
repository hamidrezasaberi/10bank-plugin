<form action="<?php echo $action; ?>" method="post">
<input type="hidden" name="merchantId" value="<?php echo $terminal_id; ?>" />
<input type="hidden" name="paymentId" value="<?php echo $order_id; ?>" />
<input type="hidden" name="amount" value="<?php echo $amount; ?>" />
<input type="hidden" name="revertURL" value="<?php echo $redirect_url; ?>" />
<div class="buttons">
<div class="right">
<input type="submit" value="<?php echo $button_confirm; ?>" class="button" />
</div> </div>
</form>