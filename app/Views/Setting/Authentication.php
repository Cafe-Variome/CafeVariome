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

<?php echo form_open($controllerName."/Authentication"); ?>

<?php foreach ($settings as $s): ?>
<div class="form-group row">
    <div class="col-3">
        <?= $s['setting_name'] ?>
    </div>
    <div class="col-6">
    <?php if($s['value'] == 'on' || $s['value'] == 'off'): ?>
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" name="<?= $s['setting_key'] ?>" id ="<?= $s['setting_key'] ?>" <?= $s['value'] == 'on' ? 'checked' : '' ?>>
            <label class="custom-control-label" for="<?= $s['setting_key'] ?>">Check</label>
            </div>
    <?php else: ?>
        <input type="text" class="form-control" name="<?= $s['setting_key'] ?>" value="<?= $s['value'] ?>">
    <?php endif; ?>
    </div>
    <div class="col-3">
        <?php if($s['info'] != null): ?>
        <button type="button" class="btn btn-info" data-toggle="tooltip" data-placement="right" title="<?= $s['info'] ?>">
            <i class="fa fa-question-circle"></i>
        </button>
        <?php endif ?>
    </div>
</div>
<?php endforeach; ?>

<div class="form-group row">
    <div class="col">
    <button type="submit" name="submit" class="btn btn-primary">
        <i class="fa fa-file"></i>  Save Settings
        </button>      
    </div>
</div>
<?php echo form_close(); ?>


<?= $this->endSection() ?>