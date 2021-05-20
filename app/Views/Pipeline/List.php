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
			<td>
			<?php if($pipeline['subject_id_location'] == '0'): ?>
				Attribute in File
			<?php elseif($pipeline['subject_id_location'] == '1'): ?> 
				File Name
			<?php endif; ?>
			</td>
			<td><?= $pipeline['subject_id_attribute_name'] ?></td>
			<td>
			<?php if($pipeline['grouping'] == '0'): ?>
				Group Individually
			<?php elseif($pipeline['grouping'] == '1'): ?> 
				Custom
			<?php endif; ?>
			</td>
			<td>
				<a href="<?php echo base_url($controllerName. '/Update'). "/" . $pipeline['id']; ?>" data-toggle="tooltip" data-placement="top" title="Edit Pipeline">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Details'). "/" . $pipeline['id']; ?>" data-toggle="tooltip" data-placement="top" title="View Pipeline">
					<i class="fa fa-list text-info"></i>
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
		<a href="<?php echo base_url($controllerName.'/Create') ?>" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-file"></i>  Create Pipeline
		</a>
	</div>
</div>
<?= $this->endSection() ?>