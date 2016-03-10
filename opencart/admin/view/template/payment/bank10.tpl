<?php echo $header; ?>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>

<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
  <div class="heading">
      <h1><?php echo $heading_title; ?></h1>
	  
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
    </div>
    
    <div class="content">

<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
    <table class="form">

      <tr>
        <td width="25%"><span class="required">*</span> <?php echo $entry_bank10_id; ?></td>
        <td><input type="text" name="bank10_id" value="<?php echo $bank10_id; ?>" />
          <br />
          <?php if ($error_bank10_id) { ?>
          <span class="error"><?php echo $error_bank10_id; ?></span>
          <?php } ?></td>
      </tr> 
	  <tr>
        <td width="25%"><span class="required">*</span> <?php echo $entry_bank10_api; ?></td>
        <td><input type="text" name="bank10_api" value="<?php echo $bank10_api; ?>" />
          <br />
          <?php if ($error_bank10_api) { ?>
          <span class="error"><?php echo $error_bank10_api; ?></span>
          <?php } ?></td>
      </tr>
	  
      <tr>
        <td><?php echo $entry_order_status; ?></td>
        <td><select name="bank10_order_status_id">
            <?php foreach ($order_statuses as $order_status) { ?>
            <?php if ($order_status['order_status_id'] == $bank10_order_status_id) { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
            <?php } else { ?>
            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
            <?php } ?>
            <?php } ?>
          </select></td>
      </tr>
      <tr>
        <td><?php echo $entry_status; ?></td>
        <td><select name="bank10_status">
            <?php if ($bank10_status) { ?>
            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
            <option value="0"><?php echo $text_disabled; ?></option>
            <?php } else { ?>
            <option value="1"><?php echo $text_enabled; ?></option>
            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
            <?php } ?>
          </select></td>
      </tr>
      <tr>
        <td><?php echo $entry_sort_order; ?></td>
        <td><input type="text" name="bank10_sort_order" value="<?php echo $bank10_sort_order; ?>" size="1" /></td>
      </tr>
    </table>
<img src="view/image/payment/bank10.png" alt="" /> 
</form>
</div>
</div>
</div>
<?php echo $footer; ?>