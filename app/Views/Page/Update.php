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

<?php echo form_open($controllerName.'/Update/'. $page_id); ?>

<div class="form-group row">
    <div class="col-8">
        <?php echo form_label('Page Title', 'title'); ?>
        <?php echo form_input($ptitle); ?>
    </div>
    <div class="col-4">
        <small class="form-text text-muted mt-5">
           The title appears on top of the page and in the title bar, next to the heading.
        </small>
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
            <i class="fa fa-file"></i>  Update Page
        </button>
	</div>
</div>


<?php echo form_close(); ?>

<?= $this->endSection() ?>