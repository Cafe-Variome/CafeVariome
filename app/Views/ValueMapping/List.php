<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> of '<?= $valueName ?>'
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
<table class="table table-bordered table-striped table-hover" id="valueMappingsTable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($valueMappings as $valueMapping): ?>
		<tr>
			<td><?= $valueMapping->name; ?></td>
			<td>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName. '/Delete'). "/" . $valueMapping->getID(); ?>">
					<i class="fa fa-trash"></i> Delete Value Mapping
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-success bg-gradient-success" href="<?= base_url($controllerName . '/Create/' . $valueId) ?>">
			<i class="fa fa-plus"></i> Create a Value Mapping
		</a>
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('/Value/List/' . $attributeId) ?>">
			<i class="fa fa-arrow-left"></i> Go Back to List of Values for <?= $attributeName ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>
