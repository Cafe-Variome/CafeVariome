<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
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
	<?php for($c = 0; $c < count($singleSignOnProviders); $c++): ?>
		<tr>
			<td><?= $singleSignOnProviders[$c]->name ?></td>
			<td><?= $singleSignOnProviders[$c]->display_name ?></td>
			<td><?= $singleSignOnProviders[$c]->port ?></td>
			<td><img src="<?= base_url('ContentAPI/SingleSignOnIcon/' . $singleSignOnProviders[$c]->getID()) ?>"></td>
			<td>
				<?= \App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper::getType($singleSignOnProviders[$c]->type) ?>
			</td>
			<td>
				<a href="<?= base_url($controllerName . '/Update/' . $singleSignOnProviders[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Edit Server">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Details'). "/" . $singleSignOnProviders[$c]->getID(); ?>" data-toggle="tooltip" data-placement="top" title="View Server">
					<i class="fa fa-eye text-info"></i>
				</a>
				<?php if($singleSignOnProviders[$c]->removable): ?>
					<a href="<?= base_url($controllerName . '/Delete/' . $singleSignOnProviders[$c]->getID()) ?>" data-toggle="tooltip" data-placement="top" title="Delete Server">
						<i class="fa fa-trash text-danger"></i>
					</a>
				<?php endif; ?>
			</td>
		</tr>
	<?php endfor; ?>
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
