<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> of '<?= $ontology->name ?>'
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
<table class="table table-bordered table-striped table-hover" id="prefixestable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($prefixes as $prefix): ?>
		<tr>
			<td><?= $prefix->name ?></td>
			<td>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName. '/Update'). "/" . $prefix->getID(); ?>">
					<i class="fa fa-edit"></i> Edit Ontology Prefix
				</a>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName. '/Delete'). "/" . $prefix->getID(); ?>">
					<i class="fa fa-trash"></i> Delete Ontology Prefix
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create/' . $ontology->getID()) ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create Ontology Prefix
		</a>
		<a href="<?= base_url('Ontology'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-project-diagram"></i> View Ontologies
		</a>
	</div>
</div>

<?= $this->endSection() ?>
