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
		<h4>List of unique attributes and values of <?= $sourceName ?></h3>
	</div>
</div>
<hr>
<div class="alert alert-danger" id="erroralert" style="display:none">
	<div class="row">
		<div class="col-9">
			<p>
				There was a problem loading the attributes and values list for this source.
			</p>
			<p id="errorinfo"> </p>
		</div>
		<div class="col-3">
			<i class="fa fa-exclamation-triangle fa-5x"></i>
		</div>
	</div>
</div>

<input type="hidden" value="<?php echo $source_id ?>" name="source_id" id="source_id">

<div class="row">
	<div class="col text-center">
		<div class="spinner-grow text-info" role="status" style="width: 5rem; height: 5rem; display:none" id="loader">
			<span class="sr-only">Loading...</span>
		</div>
	</div>
</div>
<table class="table table-bordered table-striped table-hover" id="attributestable">
	<thead>
		<tr>
			<th>Attribute</th>
			<th>File</th>
			<th>Unique Values Count</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody></tbody>
</table>

<br/>

<div class="row">
	<div class="col">
		<a href="<?php echo base_url($controllerName.'/List') ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-file"></i>  View Sources
		</a>
	</div>
</div>

<br/>

<div class="modal fade" id="valueModal" tabindex="-1" role="dialog" aria-labelledby="valueModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="valueModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		<table class="table table-bordered table-striped table-hover" id="valuestable">
			<thead>
				<tr>
					<th>Values</th>
					<th>Frequency</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>