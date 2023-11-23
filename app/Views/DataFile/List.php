<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> of '<?= $source->name ?>'
</h2>
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

<table class="table table-bordered table-striped table-hover" id="datafilestable">
	<thead>
	<tr>
		<th><input type="checkbox" id="check-master"></th>
		<th>Name</th>
		<th>Size</th>
		<th>Upload Date</th>
		<th>Records</th>
		<th>User</th>
		<th>Status</th>
		<th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach($dataFiles as $dataFile): ?>
	<tr>
		<td><?= form_checkbox(['name' => 'file_ids[]', 'class'=> 'batch-select', 'id' => 'check-' . $dataFile->getID(), 'data-filename' => $dataFile->name, 'checked' => false], $dataFile->getID()); ?></td>
		<td><?= $dataFile->name ?></td>
		<td><?= \App\Libraries\CafeVariome\Helpers\UI\SourceHelper::formatSize($dataFile->size) ?></td>
		<td><?= date("H:i:s d-m-Y T", $dataFile->upload_date) ?></td>
		<td><?= $dataFile->record_count ?></td>
		<td>
			<?= $dataFile->user_username ?>
			<br>
			(<?= $dataFile->user_first_name ?> <?= $dataFile->user_last_name ?>)
		</td>
		<td id="status-<?= $dataFile->getID(); ?>">
			<?= $dataFile->status_text ?>
		</td>
		<td id="action-<?= $dataFile->getID() ?>">
			<?php if($dataFile->status != DATA_FILE_STATUS_PROCESSING): ?>
				<div id="actionBtns-<?= $dataFile->getID() ?>" class="actionBtns">
					<a class="btn btn-sm btn-success bg-gradient-success" data-bs-toggle="modal" data-bs-target="#taskModal" data-fileid="<?= $dataFile->getID() ?>" data-filename="<?= $dataFile->name ?>">
						<i class="fa fa-play"></i> Process Data File
					</a>
					<a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName.'/Tasks'). "/" . $dataFile->getID() ?>">
						<i class="fa fa-history"></i> Task History
					</a>
					<?php if($dataFile->status == DATA_FILE_STATUS_PROCESSED): ?>
					<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?php echo base_url($controllerName.'/DeleteRecords'). "/" . $dataFile->getID() ?>" >
						<i class="fa fa-trash-alt"></i> Delete Records
					</a>
					<?php endif; ?>
					<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?php echo base_url($controllerName.'/Delete'). "/" . $dataFile->getID() ?>">
						<i class="fa fa-trash"></i> Delete Data File
					</a>
				</div>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<div class="row justify-content-between mt-2">
	<div class="col-5">
		<a href="<?= base_url($controllerName.'/Upload/' . $source->getID()) ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-upload"></i>  Upload Data File
		</a>
		<a href="<?= base_url($controllerName.'/Import/' . $source->getID()) ?>" class="btn btn-primary bg-gradient-primary">
			<i class="fas fa-file-import"></i> Import Data File(s)
		</a>
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
	</div>
	<div class="col-5">
		<button class="btn btn-primary bg-gradient-primary" id="batchProcessAllBtn" data-bs-toggle="modal" data-bs-target="#taskModal" disabled>
			<i class="fa fa-play"></i> Process Uploaded/Imported Data Files
			<span class="badge bg-secondary" id="uploadedImportedCount"></span>
			<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" id="uploadedImportedSpinner"></span>
		</button>
		<button class="btn btn-success bg-gradient-success batch-btn" id="batchProcessBtn" data-bs-toggle="modal" data-bs-target="#taskModal" disabled>
			<i class="fa fa-play"></i> Process Selected Data File(s) <span class="badge bg-secondary file-counter">0</span>
		</button>
<!--		<button class="btn btn-danger bg-gradient-danger batch-btn" id="batchDeleteFilesBtn" data-toggle="modal" data-target="" disabled>-->
<!--			<i class="fa fa-trash"></i> Delete Selected File(s) <span class="badge badge-light file-counter"></span>-->
<!--		</button>-->
	</div>
</div>
<br>
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="taskModalLabel">Process Data File(s)</h5>
			</div>
			<?= form_open('AjaxApi/ProcessFile', ['method' => 'post']) ?>
			<input type="hidden" name="fileId" id="fileId" value="-1">
			<input type="hidden" name="sourceId" id="sourceId" value="<?= $source->getID() ?>">
			<div class="modal-body">
				<div class="row mb-3">
					<div class="col-3">File(s)</div>
					<div class="col-9" id="fileName"></div>
				</div>
				<div class="row mb-3">
					<div class="col-3">Pipeline</div>
					<div class="col-5">
						<?= form_dropdown($pipeline); ?>
						<div class="invalid-feedback">
							Please select a pipeline.
						</div>
					</div>
					<div class="col-4">
						<a href="<?= base_url('Pipeline') ?>" target="_blank">View Pipelines</a>
					</div>
				</div>
				<div class="row mb-3">
					<div class="col-3"></div>
					<div class="col-9">
						<span id="statusMessage"></span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<span id="spinner" class="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true" style="display:none"></span>
				<button type="button" id="processBtn" class="btn bg-gradient-primary btn-primary">
					<i class="fa fa-play"></i> Start Processing
				</button>
				<button type="button" class="btn btn-secondary bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>

			</div>
			<?= form_close(); ?>
		</div>
	</div>
</div>

<?= $this->endSection() ?>
