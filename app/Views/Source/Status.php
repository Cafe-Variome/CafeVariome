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
		<P>Status Page for source: <?php echo $source_id ?></p>
		<p>The Status table will refresh every 5 seconds. However as long as the search box is highlighted the refresh will not occur.</p>
	</div>	
</div>
<input type="hidden" value="<?php echo $source_id ?>" name="source_id" id="source_id">

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

<?= $this->endSection() ?>