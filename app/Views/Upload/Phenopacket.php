<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<div id="load"></div>
<div class="row">
	<div class="col">
		<h4>Import phenopacket files into <?php echo $source_name; ?></h3>
		<p>This is built to only work with Phenopacket files. </p>
		<p>Up to 200 files can be uploaded in each batch. If you have more files to upload please break your upload into chunks.</p>
	</div>
</div>

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

<div id="json_errors"></div>
<form enctype="multipart/form-data" method="post" id="phenoinfo">
	<input type="hidden" name="user_id" id="user_id" value="<?= $user_id ?>" />
	<input type="hidden" id="source_id" value="<?php echo $source_id ?>" />
	<input type="hidden" name="uploader" id="uploader" value="phenopacket" />
	<input type="hidden" name="<?= csrf_token() ?>" id="csrf_token" value="<?= csrf_hash() ?>" />

	<div class="form-group row">
		<div class="col-6">
			<div class="custom-file">
				<input type="file" class="custom-file-input" name='jsonFile[]' id="jsonFile" accept=".phenopacket, .json" aria-describedby="jsonFile" multiple required>
				<label class="custom-file-label" for="jsonFile">Choose file(s)</label>
			</div>
		</div>
		<div class="col-2">
		</div>

		<div class="col-2">
			<button class="btn btn-large btn-primary bg-gradient-primary" id="uploadBtn" type="submit">
				<span class="fa fa-upload"></span> Upload File
			</button>
		</div>
		<div class="col-2">
			<div class="spinner-border text-warning" id="uploadSpinner" role="status" style="display:none;">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
	</div>
	<div class="form-group row">
	<div class="col-6">
		<select name="pipeline" id="pipeline" class="form-control">
			<option value="-1" selected>Please select a pipeline...</option>
			<?php foreach($pipelines as $pipeline): ?>
				<option value="<?= $pipeline['id'] ?>"><?= $pipeline['name'] ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="col-6"></div>
	</div>
</form>

<hr>

<table class="table table-hover table-striped" id="file_table">
	<thead>
		<tr>
			<th>File-name</th>
			<th>User</th>
			<th>Status</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody id="file_grid">
	</tbody>
</table>
<hr>
<div class="row">
	<div class="col">
		<a class="btn btn-secondary" href="<?= base_url('Source') ?>"><i class="fa fa-backward"></i> Go back</a>
	</div>
</div>

<?= $this->endSection() ?>
