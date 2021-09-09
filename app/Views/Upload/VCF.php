<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<div class="row">
	<div class="col">
	  <h4>Import VCF's into <?php echo $source_name; ?></h3>
	  <p>Config file can be  a CSV, XLS, or XLSX file with three columns with headers: FileName, Patient ID, Tissue <br> This will allow us to correctly map and classify VCF files for reference and query purposes.</p>
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

<form enctype="multipart/form-data" method="post" id="vcfinfo">
	<input type="hidden" id="source_id" value="<?php echo $source_id ?>" name="source">
	<input type="hidden" name="user_id" id="user_id" value="<?= $user_id ?>" />
	<input type="hidden" name="uploader" id="uploader" value="vcf" />
	<input type="hidden" name="<?= csrf_token() ?>" id="csrf_token" value="<?= csrf_hash() ?>" />

	<div class="form-group row">
	<div class="col-6">
		<div class="row">
			<div class="col">
				<div class="custom-file">
					<input type="file" class="custom-file-input" name="config" id="config" accept=".csv, .xls, .xlsx" required>
					<label class="custom-file-label" for="customFile">Config File to describe VCF's:</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<div class="custom-file">
					<input type="file" class="custom-file-input" name='userfile[]' id="dataFile" aria-describedby="dataFile" accept=".vcf" required multiple>
					<label class="custom-file-label" for="customFile">File(s) to submit:</label>
				</div>
			</div>
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

</hr>

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

<div id="confirmVcf" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="exampleModalLabel">Resolve VCF upload issues for Source: <?php echo $source_id; ?></h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>
	  <div class="modal-body">
		<div id="variableIssue">
		</div>
		<table class="table table-bordered table-striped table-hover" id="vcfTable">
		  <thead>
			<tr>
			  <th>FileName</th>
			  <th id="parent"><div class="ad-left">Action</div><div class="ad-right"><label id="child" onclick="checkAllToggle()"><input type="checkbox" name="applyAll" id="applyAll"> Apply to all</label></div></th>
			</tr>
		  </thead>
		  <tbody id="vcfGrid">
		  </tbody>
		</table>
		<div class="row">
		  <div class="col">
			<button class="btn btn-large btn-primary" onclick="proceedVcf()">Proceed</button>
		  </div>
		</div>
	  </div>
	</div>
  </div>
</div>
<?= $this->endSection() ?>
