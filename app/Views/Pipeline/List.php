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

<table class="table table-bordered table-striped table-hover" id="pipelinestable">
	<thead>
		<tr>
			<th>Name</th>
			<th>Subject ID Location</th>
			<th>Subject ID Attribute Name</th>
			<th>Grouping</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($pipelines as $pipeline): ?>
		<tr>
			<td><?= $pipeline->name ?></td>
			<td><?= $pipeline->subject_id_location ?></td>
			<td><?= $pipeline->subject_id_attribute_name ?></td>
			<td><?= $pipeline->grouping?></td>
			<td>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?php echo base_url($controllerName. '/Update'). "/" . $pipeline->getID(); ?>">
					<i class="fa fa-edit"></i> Edit Pipeline
				</a>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName. '/Details'). "/" . $pipeline->getID(); ?>">
					<i class="fa fa-eye"></i> View Pipeline
				</a>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?php echo base_url($controllerName. '/Delete'). "/" . $pipeline->getID(); ?>">
					<i class="fa fa-trash"></i> Delete Pipeline
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create a Pipeline
		</a>
	</div>
</div>
<?= $this->endSection() ?>
