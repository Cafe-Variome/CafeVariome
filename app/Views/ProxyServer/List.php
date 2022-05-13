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

<table class="table table-bordered table-striped table-hover" id="proxyserverstable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Port</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php for($c = 0; $c < count($proxyServers); $c++): ?>
		<tr>
			<td><?= $proxyServers[$c]->name ?></td>
			<td><?= $proxyServers[$c]->port ?></td>
			<td>
				<a href="<?= base_url($controllerName . '/Update/' . $proxyServers[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Edit Proxy Server">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Details'). "/" . $proxyServers[$c]->getID(); ?>" data-toggle="tooltip" data-placement="top" title="View Proxy Server">
					<i class="fa fa-eye text-info"></i>
				</a>
				<a href="<?= base_url($controllerName . '/Delete/' . $proxyServers[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Delete Proxy Server">
					<i class="fa fa-trash text-danger"></i>
				</a>
			</td>
		</tr>
	<?php endfor; ?>
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
