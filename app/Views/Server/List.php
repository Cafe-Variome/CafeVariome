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
		<?php foreach ($servers as $server):?>
			<tr>
				<td><?= $server->name ?></td>
				<td>
					<a target="_blank" href="<?= $server->address ?>">
						<?= $server->address ?>
					</a>
				</td>
				<td>
					<a href="<?= base_url($controllerName . '/Update/' . $server->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Edit Server">
						<i class="fa fa-edit text-warning"></i>
					</a>
					<a href="<?php echo base_url($controllerName. '/Details'). "/" . $server->getID(); ?>" data-toggle="tooltip" data-placement="top" title="View Server">
						<i class="fa fa-eye text-info"></i>
					</a>
					<?php if($server->removable): ?>
						<a href="<?= base_url($controllerName . '/Delete/' . $server->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Delete Server">
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
			<i class="fa fa-plus"></i>  Create a Server
		</a>
	</div>
</div>
<?= $this->endSection() ?>
