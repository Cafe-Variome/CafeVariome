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

<?php echo form_open($controllerName.'/Create'); ?>
    <div class="form-group">
        <?php echo form_label('Email as Username: (*)', 'email'); ?>
        <?php echo form_input($email); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('First Name: (*)', 'first_name'); ?>
        <?php echo form_input($first_name); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('Last Name: (*)', 'last_name'); ?>
        <?php echo form_input($last_name); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('Institute/Laboratory/Company Name: (*)', 'company'); ?>
        <?php echo form_input($company); ?>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?php echo form_checkbox($isadmin); ?>
            <?php echo form_label('Assign admin rights to user for this installation.', 'isadmin', array("class"=>"custom-control-label")); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?php echo form_checkbox($isremote); ?>
            <?php echo form_label('This user is a remote user from a different installation.', 'isremote', array("class"=>"custom-control-label")); ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col">
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fa fa-file"></i>  Create User
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fa fa-minus"></i> Clear
            </button>
            <a href="<?php echo base_url($controllerName.'/List'); ?>" class="btn btn-secondary" >
                <i class="fa fa-backward"></i> Go back
            </a>
        </div>
    </div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>