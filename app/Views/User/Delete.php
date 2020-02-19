<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php echo form_open($controllerName.'/Delete/'.$id); ?>
<div class="form-group">
    <span class="text-danger">
        Warning: Are you sure you want to delete user <?= $first_name . ' ' . $last_name ?>?  
    </span>
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
<div class="form-group">
    <button type="submit" name="submit" class="btn btn-primary">
        <i class="fa fa-trash"></i>  Delete User
    </button>
    <a href="<?php echo base_url($controllerName.'/List');?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>
</div>

<?php echo form_close(); ?>
<?= $this->endSection() ?>