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
			<td><?= $ontology['name']; ?></td>
			<td><?= $ontology['node_type']; ?></td>
			<td><?= $ontology['node_key']; ?></td>
			<td><?= $ontology['key_prefix']; ?></td>
			<td><?= $ontology['term_name']; ?></td>
			<td>
				<a href="<?= base_url($controllerName. '/Update'). "/" . $ontology['id']; ?>" data-toggle="tooltip" data-placement="top" title="Edit Ontology">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?= base_url($controllerName. '/Details'). "/" . $ontology['id']; ?>" data-toggle="tooltip" data-placement="top" title="View Ontology">
					<i class="fa fa-eye text-info"></i>
				</a>
				<a href="<?= base_url('OntologyRelationship/List'). "/" . $ontology['id']; ?>" data-toggle="tooltip" data-placement="top" title="View Relationships">
					<i class="fa fa-bezier-curve text-success"></i>
				</a>
				<a href="<?= base_url('OntologyPrefix/List'). "/" . $ontology['id']; ?>" data-toggle="tooltip" data-placement="top" title="View Prefixes">
					<i class="fa fa-scroll text-primary"></i>
				</a>
				<a href="<?= base_url($controllerName. '/Delete'). "/" . $ontology['id']; ?>" data-toggle="tooltip" data-placement="top" title="Delete Ontology">
					<i class="fa fa-trash text-danger"></i>
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

