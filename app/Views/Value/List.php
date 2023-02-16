<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> for '<?= $attribute_name ?>'
</h2>
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
			<td><?= $value->name ?></td>
			<td><?= $value->display_name ?></td>
			<td><?= $value->frequency ?></td>
			<td>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update') . '/' . $value->getID();?>">
					<i class="fa fa-edit"></i> Edit Value
				</a>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url('ValueMapping/List') . '/' . $value->getID();?>">
					<i class="fa fa-map-signs"></i> View Mappings
				</a>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?= base_url($controllerName . '/Details') . '/' . $value->getID();?>">
					<i class="fa fa-eye"></i> View Value
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
			<i class="fa fa-arrow-left"></i> Go Back to List of Attributes for <?= $source_name ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>
