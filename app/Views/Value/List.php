<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?> of '<?= $attribute_name ?>'</h2>
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
		<th>Frequency</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($values as $value): ?>
		<tr>
			<td><?= $value['name'] ?></td>
			<td><?= $value['display_name'] ?></td>
			<td><?= $value['frequency'] ?></td>
			<td>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?= $this->endSection() ?>
