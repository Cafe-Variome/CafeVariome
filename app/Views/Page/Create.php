<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php echo form_open($controllerName.'/Create'); ?>

<div class="form-group row">
    <div class="col-8">
        <?php echo form_label('Page Title', 'title'); ?>
        <?php echo form_input($ptitle); ?>
    </div>
    <div class="col-4">

    </div>
</div>

<div class="form-group row">
    <div class="col-8">
        <?php echo form_label('Page Content', 'pcontent'); ?>
        <?php echo form_textarea($pcontent); ?>
    </div>
    <div class="col-4">
    </div>
</div>

<div class="form-group row">
	<div class="col">
        <button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
            <i class="fa fa-file"></i>  Create Page
        </button>
	</div>
</div>

<?php echo form_close(); ?>




<?= $this->endSection() ?>