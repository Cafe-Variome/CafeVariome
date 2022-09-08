<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?> for '<?= $sourceName ?>'</h2>
	</div>
</div>
<hr>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
<input type="hidden" id="lastTaskId" value="<?= $lastTaskId ?>" />
<table class="table table-bordered table-striped">
	<tr>
		<th>Neo4J Service Status</th>
		<td>
			<?php if ($isRunning): ?>
				<span class="text-success">Running</span>
			<?php else: ?>
				<span class="text-danger">Not Running</span>
			<?php endif; ?>
		</td>
		<td></td>
	</tr>
	<tr>
		<th>Index Status</th>
		<td><?= $indexStatusText ?></td>
		<td id="action-<?= $sourceId ?>">
			<?php if($isRunning): ?>
				<?php if($dataStatus == NEO4J_DATA_STATUS_FULLY_INDEXED || $dataStatus == NEO4J_DATA_STATUS_NOT_INDEXED): ?>
					<button onclick="regenNeo4J('<?= $sourceId; ?>', true);" class="btn btn-primary bg-gradient-primary">
						<?= ($dataStatus == NEO4J_DATA_STATUS_NOT_INDEXED) ? 'Index Data' : 'Re-index Data' ?>
					</button>
				<?php endif; ?>
				<?php if($dataStatus == NEO4J_DATA_STATUS_PARTIALLY_INDEXED): ?>
					<button onclick="regenNeo4J('<?= $sourceId; ?>', false);" class="btn btn-success">Index Appended Data</button>
				<?php endif; ?>
			<?php endif; ?>
			<span id="spinner" class="spinner-border spinner-border-sm text-warning" role="status" aria-hidden="true" style="display:none"></span>
			<br>
			<span id="statusMessage"></span>
		</td>
	</tr>
	<tr>
		<th>Data Status</th>
		<td><?= $dataStatusText ?></td>
		<td id="status-<?= $sourceId ?>"></td>
	</tr>
	<tr>
		<th>Index Details</th>
		<td>
			Number of Subjects Created: <?= $indexedSubjectsCount ?></br>
			Number of Relationships Created: <?= $relationshipsCount ?></br>
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
