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
			<div class="alert alert-<?= $statusMessageType ?>">
				<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php echo form_open($controllerName.'/Create', ['name' => 'createNetwork']); ?>
<div class="form-group row">
	<div class="col-6">
	<?php echo form_label('Network Name', 'name'); ?>
	<?php echo form_input($name); ?>
	</div>
	<div class="col-6 pt-4">
		<small class="form-text text-muted">
		(no spaces allowed but underscores and dashes are accepted, uppercase characters will be converted to lowercase)
		</small>
	</div>
</div>
<div class="form-group row">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
		<i class="fa fa-plus"></i>  Create Network
	</button>
	<a href="<?php echo base_url('Network'); ?>" class="btn btn-secondary bg-gradient-secondary"><i class="fas fa-fw fa-network-wired"></i> View Networks</a>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>
