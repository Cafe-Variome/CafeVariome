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
<?php if (!is_writable(FCPATH . UPLOAD)):?>
	<div class="alert alert-danger alert-dismissible fade show">
		<div class="row">
			<div class="col-9">
				WARNING: Your upload directory is currently not writable by the webserver. In order to import records you must make this directory writable.
				<br />Please change the permissions of the following directory:
				<br /><strong><?= FCPATH . UPLOAD ?></strong>
				<br /><br />Please contact admin@cafevariome.org if you require help.
			</div>
			<div class="col-2 mt-3"><span class="fa fa-exclamation-triangle fa-5x"></span></div>
			<div class="col-1">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>
	</div>
<?php endif ?>

<div class="row">
	<div class="col">
		<div class="alert alert-warning alert-dismissible fade show" role="alert" style="display:none;" id="uploadWarningAlert">
			<p id="uploadWarningText"></p>
		</div>
	</div>
</div>

<?php echo form_open_multipart($controllerName.'/Update/' . $id); ?>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Name', 'name'); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Display Name', 'display_name'); ?>
		<?php echo form_input($display_name); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Server', 'server_id'); ?>
		<?php echo form_dropdown($server_id); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Port', 'port'); ?>
		<?php echo form_input($port); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Credential', 'credential_id'); ?>
		<?php echo form_dropdown($credential_id); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Proxy Server', 'proxy_server_id'); ?>
		<?php echo form_dropdown($proxy_server_id); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Realm', 'realm'); ?>
		<?php echo form_input($realm); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Logout URL', 'logout_url'); ?>
		<?php echo form_input($logout_url); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Type', 'type'); ?>
		<?php echo form_dropdown($type); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Post Authentication Policy', 'authentication_policy'); ?>
		<?php echo form_dropdown($authentication_policy); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Icon', 'icon'); ?>
		<div class="custom-file">
			<?php echo form_upload($icon) ?>
			<?php echo form_label('Choose icon file...', 'icon', ['class' => 'custom-file-label']); ?>
		</div>
	</div>
	<div class="col-6 pt-4">
		Maximum File Size Allowed: <span id="maxUploadSize" data-bytevalue="<?= $maxUploadSize ?>"> <?= $maxUploadSizeH ?></span> <br>
		Selected File Size: <span id="selectedFileSize">-</span>
	</div>
</div>

<div class="form-group row">
	<div class="col-3">
		<?php echo form_label('User Login', 'user_login'); ?>
		<?php echo form_checkbox($user_login); ?>
	</div>
	<div class="col-3">
		<?php echo form_label('Query', 'query'); ?>
		<?php echo form_checkbox($query); ?>
	</div>
	<div class="col-6 text-center pt-3">
		<img src="<?= base_url('ContentAPI/SingleSignOnIcon/' . $id) ?>">
	</div>
</div>
<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save" ></i> Save Changes
		</button>
		<a href="<?= base_url($controllerName) . '/List';?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-sign-in-alt"></i> View Single Sign-on Providers
		</a>
	</div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
