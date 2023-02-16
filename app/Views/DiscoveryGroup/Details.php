<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
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

<div class="row justify-content-center">
	<div class="col-auto">
		<table class="table table-bordered table-striped table-hover" id="">
			<tr>
				<th>ID</th>
				<td><?= $discoveryGroup->getID(); ?></td>
			</tr>
			<tr>
				<th>Name</th>
				<td><?= $discoveryGroup->name ?></td>
			</tr>
			<tr>
				<th>Network</th>
				<td><?= $discoveryGroup->network_name ?></td>
			</tr>
			<tr>
				<th>Description</th>
				<td><?= $discoveryGroup->description; ?></td>
			</tr>
			<tr>
				<th>Policy</th>
				<td><?= $discoveryGroup->policy; ?></td>
			</tr>
			<tr>
				<th>Assigned Sources</th>
				<td>
					<?php foreach ($sources as $source): ?>
						<a class="badge badge-light" target="_blank" href="<?= base_url('Source/Details/' . $source->getID()) ?>"> <?= $source->name ?></a>
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th>Assigned Users</th>
				<td>
					<?php foreach ($users as $user): ?>
						<a class="badge badge-light" target="_blank" href="<?= base_url('User/Details/' . $user->getID()) ?>"> <?= $user->first_name ?> <?= $user->last_name ?></a>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fas fa-fw fa-user-friends"></i> View Discovery Groups
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $discoveryGroup->getID(); ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Discovery Group
		</a>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $discoveryGroup->getID(); ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Discovery Group
		</a>
	</div>
</div>
<?= $this->endSection() ?>
