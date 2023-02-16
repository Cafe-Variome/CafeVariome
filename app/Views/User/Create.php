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

<?php echo form_open($controllerName.'/Create'); ?>
    <div class="row mb-3">
		<div class="col-6">
			<?php echo form_label('Email as Username: (*)', 'email', ['class' => 'form-label']); ?>
			<?php echo form_input($email); ?>
		</div>
		<div class="col-6">
			<?php echo form_label('Institute/Laboratory/Company Name: (*)', 'company', ['class' => 'form-label']); ?>
			<?php echo form_input($company); ?>
		</div>
    </div>
    <div class="row mb-3">
		<div class="col-6">
			<?php echo form_label('First Name: (*)', 'first_name', ['class' => 'form-label']); ?>
			<?php echo form_input($first_name); ?>
		</div>
		<div class="col-6">
			<?php echo form_label('Last Name: (*)', 'last_name', ['class' => 'form-label']); ?>
			<?php echo form_input($last_name); ?>
		</div>
    </div>
    <div class="row mb-3">
		<div class="col-12">
			<div class="custom-control custom-checkbox">
				<?php echo form_checkbox($is_admin); ?>
				<?php echo form_label('Assign admin rights to user for this installation.', 'is_admin', ["class"=>"form-check-label"]); ?>
			</div>
		</div>
    </div>
    <div class="row mb-3">
		<div class="col-12">
			<div class="custom-control custom-checkbox">
				<?php echo form_checkbox($remote); ?>
				<?php echo form_label('This user is a remote user from a different installation.', 'remote', ["class"=>"form-check-label"]); ?>
			</div>
		</div>
    </div>
    <div class="row mb-3">
        <div class="col">
            <button type="submit" name="submit" class="btn btn-success bg-gradient-success">
                <i class="fa fa-plus"></i>  Create User
            </button>
			<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary bg-gradient-secondary">
				<i class="fas fa-fw fa-user"></i> View Users
			</a>
        </div>
    </div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
