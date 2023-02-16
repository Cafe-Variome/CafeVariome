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

<?= form_open($controllerName."/Delete/" . $dataFile->getID()); ?>
<div class="row mb-3">
	<div class="col">
		<div class="alert alert-danger">
			<p class="text-lg-left bol">
				<strong>
					Do you wish to delete '<?= $dataFile->name; ?>'?
					<br>
					This action deletes the data file from the server and all associated records from the database and cannot be undone.
				</strong>
			</p>
		</div>
	</div>
</div>
<div class="row mb-3">
	<div class="col">
		<div class="form-check form-check-inline">
			<input class="form-check-input" type="radio" name="confirm" id="confirm_yes" value="yes">
			<label class="form-check-label" for="confirm_yes">Yes</label>
		</div>
		<div class="form-check form-check-inline">
			<input class="form-check-input" type="radio" name="confirm" id="confirm_no" value="no" checked>
			<label class="form-check-label" for="confirm_no">No</label>
		</div>
	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>  Delete Data File
		</button>
		<a href="<?= base_url($controllerName) . '/List/' . $dataFile->source_id;?>" class="btn btn-secondary bg-gradient-secondary">
			Cancel
		</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
