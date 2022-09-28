<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if ($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
				<?= $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<table class="table table-bordered table-striped table-hover" id="credentialstable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Username</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($credentials as $credential): ?>
	<tr>
		<td><?= $credential->name ?></td>
		<td>
		<?php if (!is_null($credential->username )): ?>
			<?= $credential->hide_username ? "[Username is hidden]" : $credential->username ?>
		<?php endif; ?>
		</td>
		<td>
			<a href="<?= base_url($controllerName . '/Update/' . $credential->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Edit Credential">
				<i class="fa fa-edit text-warning"></i>
			</a>
			<a href="<?php echo base_url($controllerName. '/Details'). "/" . $credential->getID(); ?>" data-toggle="tooltip" data-placement="top" title="View Credential">
				<i class="fa fa-eye text-info"></i>
			</a>
			<?php if ($credential->removable): ?>
				<a href="<?= base_url($controllerName . '/Delete/' . $credential->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Delete Credential">
					<i class="fa fa-trash text-danger"></i>
				</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create a Credential
		</a>
	</div>
</div>
<?= $this->endSection() ?>
