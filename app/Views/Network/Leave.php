<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<?php echo form_open($controllerName."/Leave/$network_key"); ?>
<?php echo form_hidden('name', $network_name); ?>

<div class="row mb-3">
	<div class="col">
		<div class="alert alert-danger">
			<p class="text-lg-left bol">
				<strong>
					Do you wish to leave '<?= $network_name; ?>'?
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
		<button type="submit" class="btn btn-danger bg-gradient-danger"><i class="fa fa-door-open"></i>  Leave Network</button>
		<a href="<?= base_url($controllerName);?>" class="btn btn-secondary bg-gradient-secondary">
			Cancel
		</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
