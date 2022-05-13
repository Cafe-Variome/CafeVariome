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

<div class="row justify-content-center">
	<div class="col-auto">
		<table class="table table-bordered table-striped table-hover">
			<tr>
				<th>ID</th>
				<td><?= $credential->getID(); ?></td>
			</tr>
			<tr>
				<th>Name</th>
				<td><?= $credential->name ?></td>
			</tr>
			<tr>
				<th>Username</th>
				<td><?= $credential->hide_username ? '[Username is hidden]' : $credential->username ?></td>
			</tr>
			<tr>
				<th>Password</th>
				<td>************</td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-key"></i> View Credentials
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $credential->getID(); ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Credential
		</a>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $credential->getID(); ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Credential
		</a>
	</div>
</div>

<?= $this->endSection() ?>
