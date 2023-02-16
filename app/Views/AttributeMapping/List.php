<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> of '<?= $attributeName ?>'
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
			<td><?= $attributeMapping->name; ?></td>
			<td>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName. '/Delete'). "/" . $attributeMapping->getID(); ?>">
					<i class="fa fa-trash"></i> Delete Attribute Mapping
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
