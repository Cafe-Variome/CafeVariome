<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> for '<?= $sourceName ?>'
</h2>
<hr>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
<input type="hidden" id="lastTaskId" value="<?= $lastTaskId ?>" />

<table class="table table-bordered table-striped">
	<tr>
		<th>Index Name</th>
		<td>
			<?= $indexName ?>
		</td>
		<td id="action-<?= $sourceId ?>">
			<button onclick="regenUI('<?= $sourceId; ?>');" class="btn btn-primary bg-gradient-primary">
				Index Data
			</button>
			<span id="spinner" class="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true" style="display:none"></span>
			<br>
			<span id="statusMessage"></span>
		</td>
	</tr>
	<tr>
		<th>Index Status</th>
		<td><?= $indexStatusText ?></td>
		<td id="status-<?= $sourceId ?>"></td>

	</tr>
	<tr>
		<th>Index Details</th>
		<td>
			Size: <?= $indexSize ?></br>
			Last Modified: <?= $indexCreationDate ?>
		</td>
		<td></td>
	</tr>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
		<a class="btn btn-info bg-gradient-info" href="<?= base_url('Attribute/List/' . $sourceId ) ?>">
			<i class="fa fa-database"></i> View Data Attributes for <?= $sourceName ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>
