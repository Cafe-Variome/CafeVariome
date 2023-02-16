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

<?php echo form_open($controllerName.'/Update/' . $id) ?>

<?= form_input($username_changed) ?>
<?= form_input($password_changed) ?>

<div class="row mb-3">
	<div class="col-6">
		<?= form_label('Name', 'name', ['class' => 'form-label']); ?>
		<?= form_input($name); ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="row mb-3">
	<div class="col-5">
		<?= form_label('Username', 'username', ['class' => 'form-label']); ?>
		<?= form_input($username); ?>
	</div>
	<div class="col-1">
		<br>
		<a id="changeuserlnk" href='#' onclick="enableUsernameChange()">(Change?)</a>
	</div>
	<div class="col-5">
		<?= form_label('Password', 'password', ['class' => 'form-label']); ?>
		<?= form_password($password); ?>
	</div>
	<div class="col-1">
		<br>
		<a id="changepasslnk" href='#' onclick="enablePasswordChange()">(Change?)</a>
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?= form_label('Hide Username', 'hide_username', ['class' => 'form-check-label']); ?>
		<?= form_checkbox($hide_username); ?>
	</div>
	<div class="col-6">

	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save" ></i> Save Changes
		</button>
		<a href="<?= base_url($controllerName) . '/List';?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-key"></i> View Credentials
		</a>
	</div>
</div>
<?php echo form_close(); ?>


<?= $this->endSection() ?>
