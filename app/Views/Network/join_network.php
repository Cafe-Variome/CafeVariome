<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin/index";?>">Dashboard Home</a></li>
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

<?php if ( isset($networks) ): ?>
	<?php if (array_key_exists('error', $networks) ): ?>
		<p>There are no networks available for you to join (or you are already a member of all existing networks).</p>
		<p>Go to the <a href="<?php echo base_url() . "network/create_network"; ?>">create networks page</a> if you would like to start a new network.</p>
	<?php else: ?>
		<?php echo form_open("networks/join_network", array('name' => 'joinNetwork')); ?>
		<div class="row mb-2">
			<div class="col">
				Select a network you wish to join.
			</div>
		</div>
		<div class="form-group">
			<select name="networks" id="networks" class="form-control">
			<?php foreach ($networks as $network) : ?>
				<option value="<?php echo $network->network_key; ?>" selected="selected"><?php echo $network->network_name; ?></option>
			<?php endforeach; ?>
			</select>
		</div>
			<?php $network_count = count($networks) + 1; ?>
		<div class="form-group">
			<label for="justification">Justification</label>
			<?php echo form_textarea($justification); ?>
		</div>

		<button type="submit" onclick="join_network();" class="btn btn-primary"><i class="fa fa-file"></i>  Join Network</button>
		<a href="<?php echo base_url() . "network"; ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>
		<?php echo form_close(); ?>
	<?php endif; ?>
<?php else: ?>
	<p>There was a problem finding available networks to join, please contact admin@cafevariome.org if the problem persists</p>
<?php endif; ?>
<?php 
	if (isset($result)) {
		echo "<p>Network request received successfully.</p>";
	}
?>

<?= $this->endSection() ?>
