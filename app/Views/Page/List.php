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
			<th>Creator</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($pages as $page): ?>
		<tr>
			<td><?= $page->title ?></td>
			<td><?= $page->content ?></td>
			<td><?= $page->user_first_name ?> <?= $page->user_last_name ?></td>
			<td>
				<a href="<?php echo base_url($controllerName. '/Update'). "/" . $page->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Edit Page">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a target="_blank" href="<?php echo base_url('Home/Index'). "/" . $page->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Open Page">
					<i class="fa fa-file text-primary"></i>
				</a>
				<?php if($page->removable): ?>
					<?php if($page->active): ?>
					<a href="<?php echo base_url($controllerName.'/Deactivate'). "/" . $page->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Deactivate Page">
						<i class="fa fa-eye-slash text-info"></i>
					</a>
					<?php else: ?>
						<a href="<?php echo base_url($controllerName.'/Activate'). "/" . $page->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Activate Page">
						<i class="fa fa-eye text-success"></i>
					</a>
					<?php endif ?>
				<a href="<?php echo base_url($controllerName.'/Delete'). "/" . $page->getID(); ?>" data-toggle="tooltip" data-placement="top" title="Delete Page">
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
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-file"></i>  Create a Page
		</a>
	</div>
</div>
<?= $this->endSection() ?>
