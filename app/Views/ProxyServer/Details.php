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
				<td><?= $proxyServer->getID(); ?></td>
			</tr>
			<tr>
				<th>Name</th>
				<td><?= $proxyServer->name ?></td>
			</tr>
			<tr>
				<th>Port</th>
				<td><?= $proxyServer->port ?></td>
			</tr>
			<tr>
				<th>Server</th>
				<td><a target="_blank" href="<?= base_url('Server/Details/' . $server->getID()) ?>"><?= $server->name ?> [<?= $server->address ?>]</a></td>
			</tr>
			<tr>
				<th>Credentials</th>
				<td>
					<?php if($credential != null): ?>
						<a target="_blank" href="<?= base_url('Credential/Details/' . $credential->getID()) ?>">
							<?= $credential->name ?> <?= $credential->hide_username ? '[Username is hidden]' : ' [' . $credential->username . ']' ?>
						</a>
					<?php else: ?>
					No credential selected.
					<?php endif; ?>

				</td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-ethernet"></i> View Proxy Servers
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $proxyServer->getID(); ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Proxy Server
		</a>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $proxyServer->getID(); ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Proxy Server
		</a>
	</div>
</div>

<?= $this->endSection() ?>
