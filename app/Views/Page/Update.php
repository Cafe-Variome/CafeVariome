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

<?php echo form_open($controllerName.'/Update/'. $page_id); ?>

<div class="row mb-3">
    <div class="col-8">
        <?php echo form_label('Page Title', 'title', ['class' => 'form-label']); ?>
        <?php echo form_input($ptitle); ?>
    </div>
    <div class="col-4">
        <small class="form-text text-muted mt-5">
           The title appears on top of the page and in the title bar, next to the heading.
        </small>
    </div>
</div>

<div class="row mb-3">
    <div class="col-8">
        <?php echo form_label('Page Content', 'pcontent', ['class' => 'form-label']); ?>
        <?php echo form_textarea($pcontent); ?>
    </div>
    <div class="col-4">
    </div>
</div>

<div class="row mb-3">
	<div class="col">
        <button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
            <i class="fa fa-save"></i>  Save Changes
        </button>
		<a href="<?= base_url($controllerName);?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-file"></i> View Pages
		</a>
	</div>
</div>


<?php echo form_close(); ?>

<?= $this->endSection() ?>
