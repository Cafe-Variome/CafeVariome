<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "source";?>">Sources</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
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

<table class="table table-bordered table-striped table-hover" id="file_table">
	<thead>
		<tr>
			<th>File-name</th>
			<th>User</th>
			<th>Upload Start</th>
			<th>Upload End</th>
			<th>Errors</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody id="file_grid">
	</tbody>
</table>

<?= $this->endSection() ?>