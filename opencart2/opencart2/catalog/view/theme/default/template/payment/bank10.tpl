<form action="<?php echo $action; ?>" method="post" id="payment">
<input type="hidden" name="pay" value="1" />
<div class="buttons">
  <div class="pull-right">
    <input type="button" onclick="$('#payment').submit();" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
  </div>
</div>
</form>