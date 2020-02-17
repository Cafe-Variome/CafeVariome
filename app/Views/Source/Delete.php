<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  	<li class="breadcrumb-item"><a href="<?php echo base_url('admin/index');?>">Dashboard Home</a></li>
  	<li class="breadcrumb-item"><a href="<?php echo base_url($controllerName);?>">Sources</a></li>
	<li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>

<?php echo form_open($controllerName."/Delete/" . $source_id . "/" . $source); ?>
<?php echo form_hidden(array('source' => $source)); ?>
<?php echo form_hidden(array('source_id' => $source_id)); ?>
<div class="form-group">
<span class="text-danger">Warning: Are you sure you want to delete the source '<?php echo $source; ?>' ?</span>
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

<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary">
			<i class="fa fa-trash"></i>  Delete Source
		</button>
		<a href="<?php echo base_url($controllerName);?>" class="btn btn-secondary" >
			<i class="fa fa-backward"></i> Go back
		</a>	
	</div>

</div>

<?php echo form_close(); ?>
<?= $this->endSection() ?>