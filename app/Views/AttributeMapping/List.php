<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?> of '<?= $attributeName ?>'</h2>
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
<table class="table table-bordered table-striped table-hover" id="attributeMappingsTable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($attributeMappings as $attributeMapping): ?>
		<tr>
			<td><?= $attributeMapping['name']; ?></td>
			<td>
				<a href="<?= base_url($controllerName. '/Delete'). "/" . $attributeMapping['id']; ?>" data-toggle="tooltip" data-placement="top" title="Delete Attribute Mapping">
					<i class="fa fa-trash text-danger"></i>
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-success bg-gradient-success" href="<?= base_url($controllerName . '/Create/' . $attributeId) ?>">
			<i class="fa fa-plus"></i> Create an Attribute Mapping
		</a>
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('/Attribute/List/' . $sourceId) ?>">
			<i class="fa fa-arrow-left"></i> Go Back to List of Attributes for <?= $sourceName ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>
