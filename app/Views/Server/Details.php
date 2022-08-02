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
				<td><?= $server->getID(); ?></td>
			</tr>
			<tr>
				<th>Name</th>
				<td><?= $server->name ?></td>
			</tr>
			<tr>
				<th>Address</th>
				<td><?= $server->address ?></td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-server"></i> View Servers
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $server->getID(); ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Server
		</a>
		<?php if ($server->removable): ?>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $server->getID(); ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Server
		</a>
		<?php endif; ?>
	</div>
</div>

<?= $this->endSection() ?>
