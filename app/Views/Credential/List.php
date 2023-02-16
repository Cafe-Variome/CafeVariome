<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<?php if ($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
				<?= $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<table class="table table-bordered table-striped table-hover" id="credentialstable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Username</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($credentials as $credential): ?>
	<tr>
		<td><?= $credential->name ?></td>
		<td>
		<?php if (!is_null($credential->username )): ?>
			<?= $credential->hide_username ? "[Username is hidden]" : $credential->username ?>
		<?php endif; ?>
		</td>
		<td>
			<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update/' . $credential->getID()) ?>">
				<i class="fa fa-edit"></i> Edit Credential
			</a>
			<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName. '/Details'). "/" . $credential->getID(); ?>">
				<i class="fa fa-eye"></i> View Credential
			</a>
			<?php if($credential->removable): ?>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName . '/Delete/' . $credential->getID()) ?>">
					<i class="fa fa-trash"></i> Delete Credential
				</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?= base_url($controllerName.'/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create a Credential
		</a>
	</div>
</div>
<?= $this->endSection() ?>
