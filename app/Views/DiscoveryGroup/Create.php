<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php echo form_open($controllerName. '/Create'); ?>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Network', 'network', ['class' => 'form-label']); ?>
		<?= form_dropdown($network) ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Policy', 'policy', ['class' => 'form-label']); ?>
		<?= form_dropdown($policy) ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Users', 'users', ['class' => 'form-label']); ?>
		<?= form_multiselect($users) ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Sources', 'sources', ['class' => 'form-label']); ?>
		<?= form_multiselect($sources) ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col-12">
		<?php echo form_label('Description', 'description', ['class' => 'form-label']); ?>
		<?php echo form_textarea($description); ?>
	</div>

</div>

<div class="row mb-3">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
		<i class="fa fa-plus"></i> Create Discovery Group
	</button>
		<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary bg-gradient-secondary"><i class="fas fa-fw fa-user-friends"></i> View Discovery Groups</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
