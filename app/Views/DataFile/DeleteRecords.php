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

<?= form_open($controllerName."/DeleteRecords/" . $dataFile->getID()); ?>
<div class="row mb-3">
	<div class="col">
		<div class="alert alert-danger">
			<p class="text-lg-left bol">
				<strong>
					Do you wish to delete records of '<?= $dataFile->name; ?>'?
					<br>
					This action deletes all associated data file records from the database.
					However, the file will remain on the server and data can be imported later.
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

<div class="row mb-3 row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-trash-alt"></i>  Delete Data File Records
		</button>
		<a href="<?= base_url($controllerName) . '/List/' . $dataFile->source_id;?>" class="btn btn-secondary bg-gradient-secondary">
			Cancel
		</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
