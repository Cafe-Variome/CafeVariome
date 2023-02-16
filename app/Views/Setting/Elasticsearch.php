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

<?php echo form_open($controllerName."/Elasticsearch"); ?>

<?php foreach ($settings as $s): ?>
<div class="row mb-3">
    <div class="col-3">
        <?= $s->name ?>
    </div>
    <div class="col-6">
        <input type="text" class="form-control" name="<?= $s->key ?>" value="<?= $s->value ?>">
    </div>
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
