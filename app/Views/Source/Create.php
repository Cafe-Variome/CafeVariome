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
		<p>Please enter the source information below.</p>
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
<?php echo form_open($controllerName . '/Create'); ?>

<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Source Name', 'name'); ?>
		<?php echo form_input($name); ?>
		<small class="form-text text-muted">
			The source name can only contain alphanumeric characters and spaces.
		</small>
	</div>
	<div class=col-6>
		<?php echo form_label('Owner Name', 'owner_name'); ?>
		<?php echo form_input($owner_name); ?>
	</div>
</div>
<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Owner Email', 'email'); ?>
		<?php echo form_input($email); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Source URI', 'uri'); ?>
		<?php echo form_input($uri); ?>
	</div>
</div>
<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Username', 'username'); ?>
		<?php echo form_input($username); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Password', 'password'); ?>
		<?php echo form_input($password); ?>
	</div>
</div>
<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Source Description', 'desc'); ?>
		<?php echo form_input($desc); ?>
	</div>
	<div class=col-6>
		<?php echo form_label('Status', 'status'); ?>
		<?php
		$options = array(
			'online' => 'Online',
			'offline' => 'Offline',
		);
		echo form_dropdown('status', $options, 'mysql', ['class' => 'form-control']);
		?>
	</div>
</div>
<div class="form-group row">
	<div class=col-12>
		<?php echo form_label('Long Source Description', 'long_description'); ?>
			<?php echo form_textarea($long_description); ?>
		</div>
</div>
<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Source Display Access Group', 'source_display'); ?>
		<?php echo form_multiselect('source_display[]', $srcDSPGroups , $selected_source_display, ['id'=> 'source_display', 'class' => 'form-control']); ?>
	</div>
	<div class=col-6>
		<?php echo form_label('Count Display Access Group', 'count_display'); ?>
		<?php echo form_multiselect('count_display[]', $countDSPGroups , $selected_count_display, ['id'=> 'count_display','class' => 'form-control']); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create Source
		</button>
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
	</div>
</div>

<?php echo form_close(); ?>
<?= $this->endSection() ?>
