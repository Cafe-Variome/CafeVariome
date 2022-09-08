<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
    </div>
</div>
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

<?php echo form_open($controllerName. '/Join', ['name' => 'joinNetwork']); ?>
<div class="form-group row">
	<div class="col-6">
		<?= form_label('Network', 'network') ?>
		<?= form_dropdown($network) ?>
	</div>
	<div class="col-6"></div>
</div>
<div class="form-group row">
	<div class="col-6">
		<label for="justification">Justification</label>
		<?php echo form_textarea($justification); ?>
	</div>
	<div class="col-6"></div>
</div>
<div class="form-group row">
	<div class="col">
		<button type="submit" class="btn btn-primary bg-gradient-primary"><i class="fa fa-sign-in-alt"></i>  Join Network</button>
		<a href="<?php echo base_url('Network'); ?>" class="btn btn-secondary bg-gradient-secondary"><i class="fas fa-fw fa-network-wired"></i> View Networks</a>
	</div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
