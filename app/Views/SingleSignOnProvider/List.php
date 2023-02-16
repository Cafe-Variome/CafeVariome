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

<table class="table table-bordered table-striped table-hover" id="singlesignonproviderstable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Display Name</th>
		<th>Port</th>
		<th>Icon</th>
		<th>Type</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($singleSignOnProviders as $singleSignOnProvider): ?>
		<tr>
			<td><?= $singleSignOnProvider->name ?></td>
			<td><?= $singleSignOnProvider->display_name ?></td>
			<td><?= $singleSignOnProvider->port ?></td>
			<td><img src="<?= base_url('ContentAPI/SingleSignOnIcon/' . $singleSignOnProvider->getID()) ?>"></td>
			<td>
				<?= \App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper::getType($singleSignOnProvider->type) ?>
			</td>
			<td>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update/' . $singleSignOnProvider->getID()) ?>">
					<i class="fa fa-edit"></i> Edit Provider
				</a>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName. '/Details'). "/" . $singleSignOnProvider->getID(); ?>">
					<i class="fa fa-eye"></i> View Provider
				</a>
				<?php if($singleSignOnProvider->removable): ?>
					<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName . '/Delete/' . $singleSignOnProvider->getID()) ?>">
						<i class="fa fa-trash"></i> Delete Provider
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
			<i class="fa fa-plus"></i>  Create a Single Sign-on Provider
		</a>
	</div>
</div>

<?= $this->endSection() ?>
