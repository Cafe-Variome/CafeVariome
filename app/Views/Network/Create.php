<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
    </div>
</div>
<hr>
<div class="row">
	<div class="col">
		<p>Please enter the network information below.</p>
	</div>
</div>	
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php echo form_open($controllerName.'/Create', ['name' => 'createNetwork']); ?>
<div class="form-group">
<?php echo form_label('Network Name', 'name'); ?>

<?php echo form_input($name); ?>
	<small class="form-text text-muted">
	(no spaces allowed but underscores and dashes are accepted, <br />uppercase characters will be converted to lowercase)
	</small>
</div>
<div class="form-group row">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-primary">
		<i class="fa fa-file"></i>  Create Network
	</button>
	<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a></p>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>