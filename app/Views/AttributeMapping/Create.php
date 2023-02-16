<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> for '<?= $attributeName ?>'
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
<?php echo form_open($controllerName.'/Create/' . $attributeId); ?>
<?php echo form_hidden(['attribute_id' => $attributeId]); ?>
<?php echo form_hidden(['source_id' => $sourceId]); ?>
<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i> Create Attribute Mapping
		</button>
		<a href="<?= base_url($controllerName) . '/List/' . $attributeId;?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-map-signs"></i> View Attribute Mappings of <?= $attributeName ?>
		</a>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>
