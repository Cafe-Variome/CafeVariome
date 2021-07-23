<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php echo form_open($controllerName."/Update_Threshold/$network_key"); ?>

<div class="form-group">
  <?php echo form_label('Network Threshold', 'network_threshold'); ?>
  <?php echo form_input($network_threshold); ?>
</div>

<div class="form-group row">
	<div class="col">
		<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>  Save</button>
		<a href="<?php echo base_url() . '/Network'; ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>        
	</div>
</div>


<input type="hidden" value="<?php echo $network_key; ?>" id="threshold_network_key">
<?php echo form_close(); ?>

<?= $this->endSection() ?>
