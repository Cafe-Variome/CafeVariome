<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?> of '<?= $dataFile->name ?>'</h2>
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

<table class="table table-bordered table-striped table-hover" id="taskstable">
	<thead>
	<tr>
		<th>Type</th>
		<th>Started</th>
		<th>Ended</th>
		<th>Progress</th>
		<th>Error Code</th>
		<th>Error Message</th>
		<th>User</th>
		<th>Status</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($tasks as $task): ?>
	<tr>
		<td><?= $task->type ?></td>
		<td><?= $task->started ?></td>
		<td><?= $task->ended ?></td>
		<td><?= $task->progress ?></td>
		<td><?= $task->error_code ?></td>
		<td><?= $task->error_message ?></td>
		<td>
			<a target="_blank" href="<?= base_url('User/Details/' . $task->user_id) ?>"><?= $task->user_first_name ?> <?= $task->user_last_name ?></a>
		</td>
		<td><?= $task->status ?></td>

	</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="row mt-2">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url($controllerName . '/List/' . $source->getID()) ?>">
			<i class="fa fa-file"></i> View Data Files for '<?= $source->name ?>'
		</a>

		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
	</div>
</div>
<?= $this->endSection() ?>
