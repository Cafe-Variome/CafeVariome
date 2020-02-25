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

<table class="table table-bordered table-striped table-hover" id="pagestable">
	<thead>
		<tr>
			<th>Title</th>
			<th>Content</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($pagesList as $page): ?>
		<tr>
			<td><?= $page['Title'] ?></td>
			<td><?= substr(strip_tags($page['Content']), 0, 100) ?> ...</td>
			<td>
				<a href="<?php echo base_url($controllerName. '/Update'). "/" . $page['id']; ?>" data-toggle="tooltip" data-placement="top" title="Edit Page">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a target="_blank" href="<?php echo base_url('Home/Index'). "/" . $page['id']; ?>" data-toggle="tooltip" data-placement="top" title="Open Page">
					<i class="fa fa-file text-primary"></i>
				</a>
				<?php if($page['Removable']): ?>
					<?php if($page['Active']): ?>
					<a href="<?php echo base_url($controllerName.'/Deactivate'). "/" . $page['id']; ?>" data-toggle="tooltip" data-placement="top" title="Deactivate Page">
						<i class="fa fa-eye-slash text-info"></i>
					</a>
					<?php else: ?>
						<a href="<?php echo base_url($controllerName.'/Activate'). "/" . $page['id']; ?>" data-toggle="tooltip" data-placement="top" title="Activate Page">
						<i class="fa fa-eye text-success"></i>
					</a>
					<?php endif ?>				
				<a href="<?php echo base_url($controllerName.'/Delete'). "/" . $page['id']; ?>" data-toggle="tooltip" data-placement="top" title="Delete Page">
					<i class="fa fa-trash text-danger"></i>
				</a>
				<?php endif ?>
			</td>
		</tr>		
		<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?php echo base_url($controllerName.'/Create') ?>" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-file"></i>  Create Page
		</a>
	</div>
</div>
<?= $this->endSection() ?>