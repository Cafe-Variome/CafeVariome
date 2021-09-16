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
				<a href="<?= base_url($controllerName . '/Update') . '/' . $value['id']?>" data-toggle="tooltip" data-placement="top" title="Edit Value">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?= base_url($controllerName . '/Details') . '/' . $value['id']?>" data-toggle="tooltip" data-placement="top" title="View Value">
					<i class="fa fa-eye text-info"></i>
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Attribute/List/' . $source_id) ?>">
			<i class="fa fa-arrow-left"></i> Go Back to List of Values for <?= $attribute_name ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>
