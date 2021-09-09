<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<div class="row mt-2">
	<div class="col">
		<h4>Upload a spreadsheet file for '<?php echo $source_name; ?>'</h3>
		<p>Accepted file formats are comma delimited, CSV, XLS, and XLSX. </p>
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

<form enctype="multipart/form-data" method="post" id="uploadBulk">
	<input type="hidden" id="source_id" name="source_id" value="<?= $source_id ?>">
	<input type="hidden" name="user_id" id="user_id" value="<?= $user_id ?>" />
	<input type="hidden" name="uploader" id="uploader" value="bulk" />
	<input type="hidden" name="<?= csrf_token() ?>" id="csrf_token" value="<?= csrf_hash() ?>" />
	<div class="form-group row">
		<div class="col-6">
			<div class="custom-file">
				<input type="file" class="custom-file-input" name='userfile' id="dataFile" accept=".csv, .xls, .xlsx" aria-describedby="dataFile" required>
				<label class="custom-file-label" for="dataFile">Choose file</label>
			</div>
		</div>
		<div class="col-2">
			<div class="custom-control custom-radio">
				<input type="radio" id="fActionOverwrite" name="fAction[]" value="overwrite" class="custom-control-input">
				<label class="custom-control-label" for="fActionOverwrite" data-toggle="tooltip" data-placement="right" title="By selecting this option you will delete all data currently in this source.">Overwrite</label>
			</div>
			<div class="custom-control custom-radio">
				<input type="radio" id="fActionAppend" name="fAction[]" value="append" class="custom-control-input" checked>
				<label class="custom-control-label" for="fActionAppend" data-toggle="tooltip" data-placement="right" title="By selecting this option you will not impact any prior data already within the source.">Append</label>
			</div>
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

<table class="table table-bordered table-striped table-hover" id="file_table" width="100%" cellspacing="0">
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
<div id="uploadInfoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="exampleModalLabel">Header rules for CSV/XLSX File Upload</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>
	  <div class="modal-body">
		<p>The first column must be "subject_id". The file will not be processed otherwise.</p>
		<p>Subsequent data columns will be considered a group and when inserted into the database will be linked together.</p>
		<p>When you want to make a new group please insert an empty column with the header "&lt;group_end&gt;"</p>
		<p>The final group does not require a closing &lt;group_end&gt; tag.</p>
		<img src="<?php echo base_url('resources/images/upload_info.png'); ?>" title="Visual Graphic Explaining Header Rules" />
	  </div>
	</div>
  </div>
</div>

<!-- added Dec 2018 -->
<div class="modal fade" id="duplicateResponse" tabindex="-1" role="dialog" aria-labelledby="duplicateResponseLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title">Duplicate subject IDs</h5>
	  </div>
	  <div class="modal-body">
		<p>Duplicate subject IDs have been detected for this source. Please select from the options below. "Cancel Upload" - will abort the upload of this file,leaving the current data intact.  "Append data" - will add the data from this file to current data, existing data is not altered. "Overwrite Source" - will delete all data for this source and replace with current upload</p>
	  </div>
	  <div class="modal-footer">
		<a href="#" id="cancel" class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancel Upload</a>
		<a href="#" id="append" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Append data</a>
		<a href="#" id="overwrite" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">Overwrite Source</a>
	  </div>
	</div>
  </div>
</div>

<div class="modal fade" id="confirmOverwrite" tabindex="-1" role="dialog" aria-labelledby="confirmOverwriteLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="exampleModalLabel">Confirm Overwrite</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	  </div>
	  <div class="modal-body">
		<p>This will delete all existing data in this source, continue?</p>
	  </div>
	  <div class="modal-footer">
		<a href="#" id ="cancel" class="btn btn-secondary" data-dismiss="modal" aria-hidden="true">Cancel</a>
		<a href="#" id ="overwrite" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">OK</a>
	  </div>
	</div>
  </div>
</div>
<?= $this->endSection() ?>
