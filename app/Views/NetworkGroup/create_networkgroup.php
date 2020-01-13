<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  	<li class="breadcrumb-item"><a href="<?php echo base_url() . "admin/index";?>">Dashboard Home</a></li>
  	<li class="breadcrumb-item"><a href="<?php echo base_url() . "networkgroup";?>">Network Groups</a></li>
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
		<p>Please enter the network group information below.</p>
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
<?php if ( ! isset($networks)): ?>
<div class="alert alert-info">
	<p>You do not appear to be part of any networks so cannot create network groups. You should <a href="<?php echo base_url() . "network";?>" >create or join a network</a>.</p>
</div>
<?php else: ?>
<?php echo form_open("networkgroup/create_networkgroup"); ?>

<div class="form-group">
<?php echo form_label('Network Name', 'network'); ?>
<?php
	$style = [
        'class' => 'form-control'
	];
	$options = array();
	foreach ($networks as $network) {
		$options[$network->network_key] = $network->network_name;
	}
	echo form_dropdown('network', $options, 'mysql', $style);
?>
</div>
<div class="form-group">
	<?php echo form_label('Group Name', 'group_name'); ?>

	<?php echo form_input($group_name); ?>
</div>
<div class="form-group">
	<?php echo form_label('Description', 'desc'); ?>
	<?php echo form_input($desc); ?>
</div>

<div class="form-group">
	<?php echo form_label('Group Type', 'group_type'); ?>
	<?php
		$options = array('source_display' => 'Source Display', 'count_display' => 'Count Display');
		echo form_dropdown('group_type', $options, 'mysql', $style);
	?>
</div>

<div class="form-group row">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-primary"> Create Group</button>
	<a href="<?php echo base_url() . "networkgroup";?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>	
	</div>
</div>

<?php echo form_close(); ?>
<?php endif; ?>

<?= $this->endSection() ?>