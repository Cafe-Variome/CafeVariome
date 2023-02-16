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
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?php echo base_url($controllerName. '/Update'). "/" . $page->getID(); ?>">
					<i class="fa fa-edit"></i> Edit Page
				</a>
				<a class="btn btn-sm btn-primary bg-gradient-primary" target="_blank" href="<?php echo base_url('Home/Index'). "/" . $page->getID(); ?>">
					<i class="fa fa-file"></i> Open Page
				</a>
				<?php if($page->removable): ?>
					<?php if($page->active): ?>
						<a class="btn btn-sm btn-secondary bg-gradient-secondary" href="<?php echo base_url($controllerName.'/Deactivate'). "/" . $page->getID(); ?>">
							<i class="fa fa-eye-slash"></i> Deactivate Page
						</a>
					<?php else: ?>
						<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName.'/Activate'). "/" . $page->getID(); ?>">
						<i class="fa fa-eye"></i> Activate Page
					</a>
					<?php endif ?>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?php echo base_url($controllerName.'/Delete'). "/" . $page->getID(); ?>">
					<i class="fa fa-trash"></i> Delete Page
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
