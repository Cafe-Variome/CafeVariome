<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
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

<div class="row justify-content-center">
	<div class="col-auto">
		<table class="table table-bordered table-striped table-hover" id="datapipelinedetailstable">
			<tr>
				<th>ID</th>
				<td><?= $ontology->getID(); ?></td>
			</tr>
			<tr>
				<th>Name</th>
				<td><?= $ontology->name ?></td>
			</tr>
			<tr>
				<th>Node Type</th>
				<td><?= $ontology->node_type ?></td>
			</tr>
			<tr>
				<th>Node Key</th>
				<td><?= $ontology->node_key ?></td>
			</tr>
			<tr>
				<th>Key Prefix</th>
				<td><?= $ontology->key_prefix ?></td>
			</tr>
			<tr>
				<th>Term Name</th>
				<td><?= $ontology->term_name ?></td>
			</tr>
			<tr>
				<th>Description</th>
				<td><?= $ontology->description ?></td>
			</tr>
		</table>
	</div>
</div>

<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-project-diagram"></i> View Ontologies
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $ontology->getID() ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Ontology
		</a>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $ontology->getID() ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Ontology
		</a>
	</div>
</div>
<?= $this->endSection() ?>
