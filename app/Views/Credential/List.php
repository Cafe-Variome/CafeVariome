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
	<?php for($c = 0; $c < count($credentials); $c++): ?>
	<tr>
		<td><?= $credentials[$c]->name ?></td>
		<td><?= $credentials[$c]->hide_username ? "[Username is hidden]" : $credentials[$c]->username ?></td>
		<td>
			<a href="<?= base_url($controllerName . '/Update/' . $credentials[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Edit Credential">
				<i class="fa fa-edit text-warning"></i>
			</a>
			<?php if ($credentials[$c]->removable): ?>
				<a href="<?= base_url($controllerName . '/Delete/' . $credentials[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Delete Credential">
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
			<i class="fa fa-plus"></i>  Create a Credential
		</a>
	</div>
</div>
<?= $this->endSection() ?>
