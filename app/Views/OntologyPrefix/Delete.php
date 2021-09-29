<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>

<?php echo form_open($controllerName."/Delete/" . $prefix_id); ?>
<?php echo form_hidden(['ontology_prefix_id' => $prefix_id]); ?>
	<div class="form-group">
		<span class="text-danger">Warning: Are you sure you want to delete '<?= $ontology_prefix_name; ?>'?</span>
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
			<button type="submit" name="submit" class="btn btn-danger bg-gradient-danger">
				<i class="fa fa-trash"></i>  Delete Ontology Prefix
			</button>
			<a href="<?= base_url($controllerName) . '/List/' . $ontology_id;?>" class="btn btn-secondary bg-gradient-secondary">
				Cancel
			</a>
		</div>
	</div>

<?php echo form_close(); ?>
<?= $this->endSection() ?>
