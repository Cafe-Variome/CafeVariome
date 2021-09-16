<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?> of '<?= $source_name ?>'</h2>
	</div>
</div>
<hr>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
				<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<table class="table table-bordered table-striped table-hover" id="attributestable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Display Name</th>
		<th>Source</th>
		<th>Type</th>
		<th>Storage Location</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($attributes as $attribute): ?>
		<tr>
			<td><?= $attribute['name'] ?></td>
			<td><?= $attribute['display_name'] ?></td>
			<td><?= $source_name ?></td>
			<td><?= $attribute['type'] ?></td>
			<td><?= $attribute['storage_location'] ?></td>
			<td>
				<a href="<?= base_url('Value/List') . '/' . $attribute['id']?>" data-toggle="tooltip" data-placement="top" title="View Values"><i class="fa fa-list text-info"></i></a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
	</div>
</div>
<?= $this->endSection() ?>
