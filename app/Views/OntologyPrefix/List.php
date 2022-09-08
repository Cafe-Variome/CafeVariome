<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?> of '<?= $ontology->name ?>'</h2>
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
				<a href="<?= base_url($controllerName. '/Update'). "/" . $prefix->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Edit Ontology Prefix">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?= base_url($controllerName. '/Delete'). "/" . $prefix->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Delete Ontology Prefix">
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
		<a href="<?= base_url($controllerName.'/Create/' . $ontology->getID()) ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create Ontology Prefix
		</a>
		<a href="<?= base_url('Ontology'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-project-diagram"></i> View Ontologies
		</a>
	</div>
</div>

<?= $this->endSection() ?>
