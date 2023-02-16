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
					<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update/' . $server->getID()) ?>">
						<i class="fa fa-edit"></i> Edit Server
					</a>
					<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName. '/Details'). "/" . $server->getID(); ?>">
						<i class="fa fa-eye"></i> View Server
					</a>
					<?php if($server->removable): ?>
						<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName . '/Delete/' . $server->getID()) ?>">
							<i class="fa fa-trash"></i> Delete Server
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
