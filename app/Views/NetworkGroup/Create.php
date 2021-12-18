<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

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
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php if ( ! isset($networks)): ?>
<div class="alert alert-info">
	<p>You do not appear to be part of any networks so cannot create network groups. You should <a href="<?php echo base_url("Network");?>" >create or join a network</a>.</p>
</div>
<?php else: ?>
<?php echo form_open($controllerName. '/Create'); ?>

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
	<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
		<i class="fa fa-plus"></i> Create Network Group
	</button>
		<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary bg-gradient-secondary"><i class="fas fa-fw fa-user-friends"></i> View Network Groups</a>
	</div>
</div>

<?php echo form_close(); ?>
<?php endif; ?>

<?= $this->endSection() ?>
