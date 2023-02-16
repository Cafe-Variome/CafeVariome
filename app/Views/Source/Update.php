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
<?php echo form_open($controllerName.'/Update/' . $id); ?>
<?php echo form_hidden('id', $id); ?>
<div class="row mb-3">
	<div class=col-6>
		<?php echo form_label('Source Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
		<small class="form-text text-muted">
			The source name can only contain alphanumeric characters and spaces.
		</small>
	</div>
	<div class=col-6>
		<?php echo form_label('Display Name', 'display_name', ['class' => 'form-label']); ?>
		<?php echo form_input($display_name); ?>
	</div>
</div>
<div class="row mb-3">
	<div class=col-6>
		<?php echo form_label('Owner Name', 'owner_name', ['class' => 'form-label']); ?>
		<?php echo form_input($owner_name); ?>
	</div>
	<div class=col-6>
		<?php echo form_label('Owner Email', 'owner_email', ['class' => 'form-label']); ?>
		<?php echo form_input($owner_email); ?>
	</div>

</div>
<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Source URI', 'uri', ['class' => 'form-label']); ?>
		<?php echo form_input($uri); ?>
	</div>
	<div class=col-6>
		<?php echo form_label('Status', 'status', ['class' => 'form-label']); ?>
		<?php echo form_dropdown($status); ?>
	</div>
</div>

<div class="row mb-3">
	<div class=col-12>
		<?php echo form_label('Source Description', 'description', ['class' => 'form-label']); ?>
		<?php echo form_textarea($description); ?>
	</div>
</div>
<div class="row mb-3">
	<div class="col">
		<button type="submit" onclick="select_groups()" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save" ></i>  Save Changes
		</button>
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
	</div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
