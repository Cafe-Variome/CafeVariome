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
<?= form_open($controllerName . '/Update/' . $id) ?>
<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Attribute Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
		<small class="form-text text-muted">
			You cannot edit attribute name as it will be used to index the data associated with it.
		</small>
	</div>
	<div class="col-6">
	</div>
</div>
<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Display Name', 'display_name', ['class' => 'form-label']); ?>
		<?php echo form_input($display_name); ?>
		<small class="form-text text-muted">
			This name will be shown in the query interface.
		</small>
	</div>
	<div class="col-6">
	</div>
</div>
<div class="row mb-3">
	<div class="col">
		<div class="custom-control custom-checkbox">
			<?php echo form_checkbox($show_in_interface); ?>
			<?php echo form_label('Show in Query Interface', 'show_in_interface',  ["class"=>"form-check-label"]) ?>
		</div>
	</div>
</div>
<div class="row mb-3">
	<div class="col">
		<div class="custom-control custom-checkbox">
			<?php echo form_checkbox($include_in_interface_index); ?>
			<?php echo form_label('Include in Query Interface Index', 'include_in_interface_index',  ["class"=>"form-check-label"]) ?>
		</div>
	</div>
</div>
<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save"></i>  Save Attribute
		</button>
		<a href="<?php echo base_url($controllerName.'/List/' . $source_id); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-arrow-left"></i> Go Back to Attributes List
		</a>
	</div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
