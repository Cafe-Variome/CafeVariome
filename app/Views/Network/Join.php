<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
    </div>
</div>
<hr>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php if (isset($networks)): ?>
	<?php if (array_key_exists('error', $networks)): ?>
		<p>There are no networks available for you to join (or you are already a member of all existing networks).</p>
		<p>Go to the <a href="<?php echo base_url($controllerName. '/Create'); ?>">create networks page</a> if you would like to start a new network.</p>
	<?php else: ?>
		<?php echo form_open($controllerName. '/Join', ['name' => 'joinNetwork']); ?>
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

		<button type="submit" class="btn btn-primary"><i class="fa fa-file"></i>  Join Network</button>
		<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>
		<?php echo form_close(); ?>
	<?php endif; ?>
<?php else: ?>
	<p>There was a problem finding available networks to join, please contact admin@cafevariome.org if the problem persists</p>
<?php endif; ?>


<?= $this->endSection() ?>
