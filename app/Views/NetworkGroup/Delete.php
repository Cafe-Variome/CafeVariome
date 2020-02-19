<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="row">
    <div class="col">
        Are you sure you want to delete the group?
    </div>
</div>
<?php echo form_open($controllerName . "/Delete/" . $group_id); ?>
<div class="form-check">
    <input type="radio" name="confirm" value="yes" class="form-check-input">
    <label for="confirm" class="form-check-label">Yes:</label>
</div>
<div class="form-check">
    <input type="radio" name="confirm" value="no" class="form-check-input" checked>
    <label for="confirm" class="form-check-label">No:</label>
</div>
<?php echo form_hidden($csrf); ?>
<?php echo form_hidden(array('id' => $group_id)); ?>

<br/>

<div class="row">
    <div class="col">
        <button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-trash"></i>  Delete Group</button>
        <a href="<?php echo base_url($controllerName);?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>
    </div>
</div>



<?php echo form_close(); ?>
<?= $this->endSection() ?>