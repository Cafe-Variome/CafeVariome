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
        <h4>Import spreadsheet files from server for '<?php echo $source_name; ?>'</h3>
        <p>Accepted file formats are comma delimited, CSV, XLS, and XLSX. </p>
    </div>
</div>

<form method="post" id="importFiles">
    <input type="hidden" id="source_id" name="source_id" value="<?= $source_id ?>">
    <input type="hidden" name="user_id" id="user_id" value="<?= $user_id ?>" />
	<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

    <div class="form-group row">
        <div class="col-6">
            <input type="text" class="form-control" id="lookupPath" placeholder="Absolute path to directory containing files...">
            <div class="invalid-feedback">
                Please provide a valid path on the server.
            </div>
        </div>
        <div class="col-2">
            <span id="lookupCount"></span>
            <span id="spinner" class="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true" style="display:none"></span>
        </div>
        <div class="col-4">
            <button class="btn btn-large btn-secondary bg-gradient-secondary" id="lookupBtn" onClick="lookupDir()">
                <span class="fa fa-search"></span> Lookup
            </button>
            <button class="btn btn-large btn-primary bg-gradient-primary" id="importBtn" onClick="importDir()" style="display:none;">
                <span class="fa fa-file-import"></span> Import Files
            </button>
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
            <div class="invalid-feedback">Please select a pipeline.</div>
        </div>
        <div class="col-6"></div>
    </div>
</form>

<table class="table table-bordered table-striped table-hover" id="file_table" width="100%" cellspacing="0">
  <thead>
	<tr>
        <th><input type="checkbox" class="chkBxMaster"></th>
		<th>File-name</th>
		<th>User</th>
		<th>Status</th>
		<th>Action</th>
	</tr>
  </thead>
  <tbody id="file_grid">
  </tbody>
</table>
<div class="row">
	<div class="col">
		<button class="btn btn-primary" id="batchProcessBtn" onclick="processSelectedFiles()" disabled>
			<i class="fa fa-redo-alt"></i> Process Selected Files <span class="badge badge-light">0</span>
		</button>

		<button class="btn btn-primary" id="batchProcessPendingBtn" onclick="processPendingFiles()">
			<i class="fa fa-redo-alt"></i> Process Pending Files <span class="badge badge-light">0</span>
		</button>
	</div>
</div>
<?= $this->endSection() ?>
