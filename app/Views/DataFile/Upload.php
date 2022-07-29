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

<h4> Upload Data Files to '<?= $source->name ?>'</h4>
<h5>Accepted file formats are: <?= $allowedFormats ?></h5>
<br>
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

<?php echo form_open_multipart($controllerName.'/Upload/' . $source->getID()); ?>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Data File', 'name'); ?>
		<div class="custom-file">
			<?php echo form_upload($name) ?>
			<?php echo form_label('Choose data file...', 'name', ['class' => 'custom-file-label']); ?>
		</div>
	</div>
	<div class="col-6 pt-4">
		Maximum File Size Allowed: <span id="maxUploadSize" data-bytevalue="<?= $maxUploadSize ?>"> <?= $maxUploadSizeH ?></span> <br>
		Selected File Size: <span id="selectedFileSize">-</span>
	</div>
</div>

<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" id="uploadBtn" class="btn btn-success bg-gradient-success">
			<i class="fa fa-upload"></i> Upload Data File
		</button>
		<a href="<?= base_url($controllerName) . '/List/' . $source->getID();?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-file"></i> View Data Files for <?= $source->name ?>
		</a>
	</div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
