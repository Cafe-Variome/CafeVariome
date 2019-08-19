<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "network";?>">Networks</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<div class="row">
	<div class="col">
		<p>Please enter the network information below.</p>
	</div>
</div>	
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php echo form_open("network/create_network", array('name' => 'createNetwork')); ?>
<div class="form-group">
<?php echo form_label('Network Name', 'name'); ?>

<?php echo form_input($name); ?>
	<small class="form-text text-muted">
	(no spaces allowed but underscores and dashes are accepted, <br />uppercase characters will be converted to lowercase)
	</small>
</div>
<div class="form-group row">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-primary">
		<i class="fa fa-file"></i>  Create Network
	</button>
	<a href="<?php echo base_url() . "network"; ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a></p>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>