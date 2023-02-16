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

<div class="row justify-content-center">
	<div class="col-auto">
		<table class="table table-bordered table-striped table-hover">
			<tr>
				<th>ID</th>
				<td><?= $singleSignOnProvider->getID(); ?></td>
			</tr>
			<tr>
				<th>Name</th>
				<td><?= $singleSignOnProvider->name ?></td>
			</tr>
			<tr>
				<th>Display Name</th>
				<td><?= $singleSignOnProvider->display_name ?></td>
			</tr>
			<tr>
				<th>Server</th>
				<td>
					<a target="_blank" href="<?= $server->isNull() ? '' : base_url('Server/Details/' . $server->getID())?>">
						<?= $server->isNull() ? '' : $server->name . '[' . $server->address . ']' ?>
					</a>
				</td>
			</tr>
			<tr>
				<th>Port</th>
				<td><?= $singleSignOnProvider->port ?></td>
			</tr>
			<tr>
				<th>Type</th>
				<td><?= \App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper::getType($singleSignOnProvider->type) ?></td>
			</tr>
			<tr>
				<th>Post Authentication Policy</th>
				<td><?=  \App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper::getPostAuthenticanPolicy($singleSignOnProvider->authentication_policy) ?></td>
			</tr>
			<tr>
				<th>Credential</th>
				<td>
					<a target="_blank" href="<?= $credential->isNull() ? '' : base_url('Credential/Details/' . $credential->getID())?>">
						<?= $credential->isNull() ? 'No credential is selected.' : $credential->name . '[' . ($credential->hide_username ? 'Username is hidden' : $credential->username) . ']' ?>
					</a>
				</td>
			</tr>
			<tr>
				<th>Proxy Server</th>
				<td>
					<a target="_blank" href="<?= $proxyServer->isNull() ? '' : base_url('ProxyServer/Details/' . $proxyServer->getID())?>">
						<?= $proxyServer->isNull() ? 'No proxy server is selected.' : $proxyServer->name ?>
					</a>
				</td>
			</tr>
			<tr>
				<th>User Login</th>
				<td><?= $singleSignOnProvider->user_login ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-times text-danger'></i>" ?></td>
			</tr>
			<tr>
				<th>Query</th>
				<td><?= $singleSignOnProvider->query ? "<i class='fa fa-check text-success'></i>" : "<i class='fa fa-times text-danger'></i>" ?></td>
			</tr>
			<tr>
				<th>Icon</th>
				<td><img src="<?= base_url('ContentAPI/SingleSignOnIcon/' . $singleSignOnProvider->getID()) ?>"></td>
			</tr>
		</table>
	</div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-sign-in-alt"></i> View Single Sign-on Providers
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $singleSignOnProvider->getID(); ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Single Sign-on Provider
		</a>
		<?php if ($singleSignOnProvider->removable): ?>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $singleSignOnProvider->getID(); ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Single Sign-on Provider
		</a>
		<?php endif; ?>
	</div>
</div>

<?= $this->endSection() ?>
