<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<div class="row justify-content-center">
	<div class="col-auto">
		<table class="table table-bordered table-striped table-hover" id="">
			<tr>
				<th>Attribute Name:</th>
				<td><?= $attribute->name ?></td>
			</tr>
			<tr>
				<th>Display Name:</th>
				<td><?= $attribute->display_name ?></td>
			</tr>
			<tr>
				<th>Type</th>
				<td><?= $attribute->type_text ?></td>
			</tr>
			<?php if (
					$attribute->type == ATTRIBUTE_TYPE_NUMERIC_REAL ||
					$attribute->type == ATTRIBUTE_TYPE_NUMERIC_INTEGER ||
					$attribute->type == ATTRIBUTE_TYPE_NUMERIC_NATURAL
			): ?>
				<tr>
					<th>Minumum</th>
					<td><?= $attribute->min ?></td>
				</tr>
				<tr>
					<th>Maximum</th>
					<td><?= $attribute->max ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th>Storage Location</th>
				<td><?= $attribute->storage_location ?></td>
			</tr>
			<tr>
				<th>Show in Query Interface</th>
				<td><?= $attribute->show_in_interface ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-times text-danger'></i>" ?></td>
			</tr>
			<tr>
				<th>Include in Query Interface Index</th>
				<td><?= $attribute->include_in_interface_index  ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-times text-danger'></i>" ?></td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Attribute/List/' . $attribute->source_id) ?>">
			<i class="fa fa-arrow-left"></i> Go Back to List of Attributes for <?= $attribute->source_name?>
		</a>

		<a class="btn btn-warning bg-gradient-warning" href="<?= base_url('Attribute/Update/' . $attribute->getID()) ?>">
			<i class="fa fa-edit"></i> Edit Attribute
		</a>
	</div>
</div>
<?= $this->endSection() ?>
