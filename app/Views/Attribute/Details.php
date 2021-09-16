<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<div class="row justify-content-center">
	<div class="col-auto">
		<table class="table table-bordered table-striped table-hover" id="">
			<tr>
				<th>Attribute Name:</th>
				<td><?= $name ?></td>
			</tr>
			<tr>
				<th>Display Name:</th>
				<td><?= $display_name ?></td>
			</tr>
			<tr>
				<th>Type</th>
				<td><?= $type ?></td>
			</tr>
			<tr>
				<th>Storage Location</th>
				<td><?= $storage_location ?></td>
			</tr>
			<tr>
				<th>Show in Query Interface</th>
				<td><?= $show_in_interface ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-times text-danger'></i>" ?></td>
			</tr>
			<tr>
				<th>Include in Query Interface Index</th>
				<td><?= $include_in_interface_index  ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-times text-danger'></i>" ?></td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Attribute/List/' . $source_id) ?>">
			<i class="fa fa-arrow-left"></i> Go Back to List of Attributes for <?= $source_name?>
		</a>

		<a class="btn btn-warning bg-gradient-warning" href="<?= base_url('Attribute/Update/' . $attribute_id) ?>">
			<i class="fa fa-edit"></i> Edit Attribute
		</a>
	</div>
</div>
<?= $this->endSection() ?>
