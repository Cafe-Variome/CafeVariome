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
		<h4>Import records for <?php echo $source_name; ?></h3>
		<p>Accepted file formats are tab delimited, csv & xlsx. </p>
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
  <input type="hidden" id="source_id" name="source_id" value="<?php echo $source_id ?>">
  <input type="hidden" name="user_id" id="user_id" value="<?= $user_id ?>" />

<div class="form-group row">
	<div class="col-6">
		<div class="custom-file">
			<input type="file" class="custom-file-input" name='userfile' id="dataFile" accept=".csv, .xls, .xlsx" aria-describedby="dataFile" required>
			<label class="custom-file-label" for="dataFile">Choose file</label>
		</div>

		<div >
			<select name="config"  id="config" required>
				<?php foreach($configs as $c):?>
				<option value="<?php echo $c?>"><?php echo $c?></option>
				<?php endforeach;?>
            </select>
        </div>
	</div>
	<div class="col-2">
		<div class="custom-control custom-radio">
			<input type="radio" id="fActionOverwrite" name="fAction[]" value="overwrite" class="custom-control-input">
			<label class="custom-control-label" for="fActionOverwrite" data-toggle="tooltip" data-placement="right" title="By selecting this option you will delete all data currently in this source.">Overwrite</label>
		</div>
		<div class="custom-control custom-radio">
			<input type="radio" id="fActionAppend" name="fAction[]" value="append" class="custom-control-input">
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
  <div class="col">
  </div>
</div>
</form>

<hr>

<div class="row">
  <div class="col">
	<p>The Status table will refresh every 5 seconds. However as long as the search box is highlighted the refresh will not occur.</p>
  </div>
</div>

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