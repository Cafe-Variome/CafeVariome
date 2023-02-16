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

<table class="table table-bordered table-striped table-hover" id="proxyserverstable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Port</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($proxyServers as $proxyServer): ?>
		<tr>
			<td><?= $proxyServer->name ?></td>
			<td><?= $proxyServer->port ?></td>
			<td>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update/' . $proxyServer->getID()) ?>">
					<i class="fa fa-edit"></i> Edit Proxy Server
				</a>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName. '/Details'). "/" . $proxyServer->getID(); ?>">
					<i class="fa fa-eye"></i> View Proxy Server
				</a>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName . '/Delete/' . $proxyServer->getID()) ?>">
					<i class="fa fa-trash"></i> Delete Proxy Server
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create a Proxy Server
		</a>
	</div>
</div>
<?= $this->endSection() ?>
