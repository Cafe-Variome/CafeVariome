<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if($message): ?>
<div class="alert alert-info">
    <?php echo $message; ?>
</div>
<?php endif ?>
<?php echo form_open($controllerName."/Settings"); ?>

<?php foreach ($settings as $s): ?>
<div class="form-group row">
    <div class="col-3">
        <?= $s['setting_name'] ?>
    </div>
    <div class="col-6">
        <input type="text" class="form-control" name="<?= $s['setting_key'] ?>" value="<?= $s['value'] ?>">
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
        <a href="<?php echo base_url('Admin/Index'); ?>" class="btn btn-secondary" >
            <i class="fa fa-backward"></i> Go back
        </a>        
    </div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>