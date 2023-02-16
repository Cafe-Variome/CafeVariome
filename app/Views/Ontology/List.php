<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
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

<table class="table table-bordered table-striped table-hover" id="ontologiestable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Node Type</th>
		<th>Node Key</th>
		<th>Key Prefix</th>
		<th>Term Name</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($ontologies as $ontology): ?>
		<tr>
			<td><?= $ontology->name; ?></td>
			<td><?= $ontology->node_type; ?></td>
			<td><?= $ontology->node_key; ?></td>
			<td><?= $ontology->key_prefix; ?></td>
			<td><?= $ontology->term_name; ?></td>
			<td>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName. '/Update'). "/" . $ontology->getID(); ?>">
					<i class="fa fa-edit"></i> Edit Ontology
				</a>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?= base_url($controllerName. '/Details'). "/" . $ontology->getID(); ?>">
					<i class="fa fa-eye"></i> View Ontology
				</a>
				<a class="btn btn-sm btn-success bg-gradient-success" href="<?= base_url('OntologyRelationship/List'). "/" . $ontology->getID(); ?>">
					<i class="fa fa-bezier-curve"></i> View Relationships
				</a>
				<a class="btn btn-sm btn-primary bg-gradient-primary" href="<?= base_url('OntologyPrefix/List'). "/" . $ontology->getID(); ?>">
					<i class="fa fa-scroll"></i> View Prefixes
				</a>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName. '/Delete'). "/" . $ontology->getID(); ?>">
					<i class="fa fa-trash"></i> Delete Ontology
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create an Ontology
		</a>
	</div>
</div>

<?= $this->endSection() ?>

