<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "source";?>">Sources</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<p>Please enter the source information below.</p>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php
	$hidden = array('source_id' => $source_id);
	echo form_open("source/edit_source", '', $hidden);
?>

<div class="form-group">
<?php echo form_label('Source Name', 'name'); ?>

	<?php echo form_input($name); ?>
	<small class="form-text text-muted">
		(no spaces allowed but underscores and dashes are accepted, <br />uppercase characters will be converted to lowercase)
	</small>
</div>
<div class="form-group">
	<?php echo form_label('Owner Email', 'email'); ?>
	<?php echo form_input($email); ?>
</div>
<div class="form-group">
	<?php echo form_label('Source URI', 'uri'); ?>
	<?php echo form_input($uri); ?>
</div>
<div class="form-group">
	<?php echo form_label('Source Description', 'desc'); ?>
	<?php echo form_input($desc); ?>
</div>
<div class="form-group">
	<?php echo form_label('Long Source Description', 'long_description'); ?>
	<?php echo form_textarea($long_description); ?>
</div>
<div class="form-group">
	<?php echo form_label('Status', 'status'); ?>

	<?php
	$style = [
        'class' => 'form-control'
	];
	$options = array(
		'online' => 'Online',
		'offline' => 'Offline',
	);
	echo form_dropdown('status', $options, $source_data['status'], $style);
	?>
</div>
<?php if (array_key_exists('error', $groups)): ?>
<div class="row">
	<div class="col">
		There are no network groups available to this installation.
	</div>
</div>
<?php else: ?>

<div class="row">
	<div class="col">
		<h4>Source Display Groups</h4>
	</div>
</div>
<div class="row">
	<div class="col">
		Select groups that can access restrictedAccess variants in this source (control click to select multiple):
	</div>
</div>
<div class="form-group row">
	<div class="col">
		<select size="5" multiple id="sdg_left">
    	</select>	
	</div>
	<div class="col">
		<br><input type="button" value="&gt;&gt;"/><br><br>
        <input type="button" value="&lt;&lt;"/>	
	</div>
	<div class="col">
		<select size="5" multiple id="sdg_right" name="groups[]" class="groupsSelected">
		</select>	
	</div>
</div>
<div class="row">
	<div class="col">
		Count Display Groups
	</div>
</div>
<div class="form-group row">
	<div class="col">
		<select size="5" multiple id="cdg_left">
        </select>
	</div>
	<div class="col">
		<br><input type="button" value="&gt;&gt;"/><br><br>
        <input type="button" value="&lt;&lt;"/>
	</div>
	<div class="col">
		<select size="5" multiple id="cdg_right" name="groups[]" class="groupsSelected">
        </select>
	</div>
</div>
<?php foreach ($groups as $group ):
		if ($group['group_type'] === "source_display"):
			if(isset($selected_groups) && array_key_exists($group['id'], $selected_groups)): ?>
				<script type="text/javascript">
					$("#sdg_right").append($("<option></option>")
					.attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
					.text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
				</script>
			<?php else: ?>
				<script type="text/javascript">
					$("#sdg_left").append($("<option></option>")
					.attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
					.text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
				</script>
			<?php endif;
		elseif ($group['group_type'] === "count_display"):
			if(isset($selected_groups) && array_key_exists($group['id'], $selected_groups)): ?>
				<script type="text/javascript">
					$("#cdg_right").append($("<option></option>")
					.attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
					.text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
				</script>
			<?php else: ?>
				<script type="text/javascript">
					$("#cdg_left").append($("<option></option>")
					.attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
					.text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
				</script>
			<?php endif;
		endif;
	endforeach; ?>
<?php endif; ?>
<div class="form-group row">
	<div class="col">
		<button type="submit" onclick="select_groups()" name="submit" class="btn btn-primary">
			<i class="fa fa-file"></i>  Save Source
		</button>
		<a href="<?php echo base_url() . "source/sources"; ?>" class="btn btn-secondary" >
			<i class="fa fa-backward"></i> Go back
		</a>
	</div>
</div>
<?= $this->endSection() ?>