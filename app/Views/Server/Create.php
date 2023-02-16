<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<?php if ($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
				<?= $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php echo form_open($controllerName.'/Create'); ?>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Address', 'address', ['class' => 'form-label']); ?>
		<?php echo form_input($address); ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i> Create Server
		</button>
		<a href="<?= base_url($controllerName) . '/List';?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-server"></i> View Servers
		</a>
	</div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
