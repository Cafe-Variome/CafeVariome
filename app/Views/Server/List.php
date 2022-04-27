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

<table class="table table-bordered table-striped table-hover" id="serverstable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Address</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
		<?php for($c = 0; $c < count($servers); $c++): ?>
			<tr>
				<td><?= $servers[$c]->name ?></td>
				<td>
					<a target="_blank" href="<?= $servers[$c]->address ?>">
						<?= $servers[$c]->address ?>
					</a>
				</td>
				<td>
					<a href="<?= base_url($controllerName . '/Update/' . $servers[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Edit Server">
						<i class="fa fa-edit text-warning"></i>
					</a>
					<?php if($servers[$c]->removable): ?>
						<a href="<?= base_url($controllerName . '/Delete/' . $servers[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Delete Server">
							<i class="fa fa-trash text-danger"></i>
						</a>
					<?php endif; ?>
				</td>
			</tr>
		<?php endfor; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create a Server
		</a>
	</div>
</div>
<?= $this->endSection() ?>
