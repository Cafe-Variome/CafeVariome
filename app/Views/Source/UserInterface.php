<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?> for '<?= $sourceName ?>'</h2>
	</div>
</div>
<hr>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
<table class="table table-bordered table-striped">
	<tr>
		<th>Index Name</th>
		<td>
			<?= $indexName ?>
		</td>
		<td id="status-<?= $sourceId ?>"></td>
	</tr>
	<tr>
		<th>Index Status</th>
		<td><?= $indexStatusText ?></td>
		<td id="action-<?= $sourceId ?>">
			<button onclick="regenUI('<?= $sourceId; ?>');" class="btn btn-primary bg-gradient-primary">
				Index Data
			</button>
		</td>
	</tr>
	<tr>
		<th>Index Details</th>
		<td>
			Size: <?= $indexSize ?></br>
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
