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

<?php echo form_open($controllerName."/Neo4J"); ?>

<?php foreach ($settings as $s): ?>
<div class="row mb-3">
    <div class="col-3">
		<?= $s->name ?>
    </div>
	<?php if($s->value == 'on' || $s->value == 'off'): ?>
		<div class="col-3">
			<div class="form-check">
				<input type="checkbox" class="form-check-input" name="<?= $s->setting_key ?>" id ="<?= $s->setting_key ?>" <?= $s->value == 'on' ? 'checked' : '' ?>>
				<label class="form-check-label" for="<?= $s->key ?>"></label>
			</div>
		</div>
	<?php else: ?>
		<div class="col-6">
			<input type="text" class="form-control" name="<?= $s->key ?>" value="<?= $s->value ?>">
		</div>
	<?php endif; ?>
	<div class="col-3">
		<?php if($s->info != null): ?>
			<button type="button" class="btn btn-info" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="<?= $s->info ?>">
				<i class="fa fa-question-circle"></i>
			</button>
		<?php endif ?>
	</div>
</div>
<?php endforeach; ?>

<div class="row mb-3">
    <div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save"></i>  Save Settings
		</button>
    </div>
</div>
<?php echo form_close(); ?>


<?= $this->endSection() ?>
