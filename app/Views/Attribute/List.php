<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
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
		<th>Type</th>
		<th>Source</th>
		<th>Storage Location</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($attributes as $attribute): ?>
		<tr>
			<td><?= $attribute['name'] ?></td>
			<td><?= $attribute['display_name'] ?></td>
			<td><?= $attribute['type'] ?></td>
			<td><?= $attribute['source_name'] ?></td>
			<td><?= $attribute['storage_location'] ?></td>
			<td>
				<a href=""><i class="fa fa-list text-info"></i></a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?= $this->endSection() ?>
