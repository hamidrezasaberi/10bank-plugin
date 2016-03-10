<?php echo $header; ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/tables.css" />

<?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<h1><?php echo $text_heading; ?></h1>
	
	<div class="content">
		<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
		<?php if (!$error_warning) { ?>
			<center>
				<table style="width: 100%;">
					<tr>
						<td width="150px"><?php echo $text_results; ?></td>
						<td><?php echo isset($authority) ? $authority : ''; ?></td>
					</tr>
				</table>
			</center>
		<?php } ?>
	</div>
	<div class="buttons">
		<div class="left"><a href="<?php echo $continue; ?>" class="button"><span><?php echo $button_continue; ?></span></a></div>
	</div>
<?php echo $content_bottom; ?></div>
<?php echo $footer; ?>