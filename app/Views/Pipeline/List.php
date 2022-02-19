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
		<?php foreach ($pipelinesList as $pipeline): ?>
		<tr>
			<td><?= $pipeline['name'] ?></td>
			<td><?= $pipeline['subject_id_location'] ?></td>
			<td><?= $pipeline['subject_id_attribute_name'] ?></td>
			<td><?= $pipeline['grouping']?></td>
			<td>
				<a href="<?php echo base_url($controllerName. '/Update'). "/" . $pipeline['id']; ?>" data-toggle="tooltip" data-placement="top" title="Edit Pipeline">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Details'). "/" . $pipeline['id']; ?>" data-toggle="tooltip" data-placement="top" title="View Pipeline">
					<i class="fa fa-eye text-info"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Delete'). "/" . $pipeline['id']; ?>" data-toggle="tooltip" data-placement="top" title="Delete Pipeline">
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
			<i class="fa fa-plus"></i>  Create a Pipeline
		</a>
	</div>
</div>
<?= $this->endSection() ?>
