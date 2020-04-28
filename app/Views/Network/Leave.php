<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>

<?php echo form_open($controllerName."/Leave/$network_key"); ?>
<?php echo form_hidden('name', $network_name); ?>                

<div class="form-group">
  <span class="text-danger">Warning: Are you sure you want to leave <?= $network_name ?>?</span>
</div>

<div class="form-group">
	<div class="form-check form-check-inline">
		<input class="form-check-input" type="radio" name="confirm" value="yes">
		<label class="form-check-label" for="confirm">Yes</label>
	</div>
	<div class="form-check form-check-inline">
		<input class="form-check-input" type="radio" name="confirm" value="no" checked>
		<label class="form-check-label" for="confirm">No</label>
	</div>
</div>
<div class="form-group row">
	<div class="col">
		<button type="submit" class="btn btn-primary"><i class="fa fa-door-open"></i>  Leave Network</button>
		<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>        
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>