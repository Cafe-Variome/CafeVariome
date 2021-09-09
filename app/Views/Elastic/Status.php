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
  Elasticsearch Service Status:
  <?php if ($isRunning): ?>
    <span class="text-success">Running</span>
  <?php else: ?>
    <span class="text-danger">Not Running</span>
  <?php endif; ?>
  </div>
</div>
<br/>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
<table class="table table-bordered table-hover table-striped" id="index_table">
  <thead>
    <tr>
        <th>ElasticSearch Index Name</th>
        <th>Source Name</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
  </thead>
  <tbody id="index_grid">
    <?php foreach ($elastic_update as $row): ?>
    <tr id="index_<?php echo $row['source_id']; ?>">
      <td><?= $indexPrefix."_".$row['source_id']; ?></td>
      <td><?= $row['name'] ?></td>
      <?php if (!$isRunning): ?>
          <td style="background-color: lightgray;">
            Unknown
          </td>
          <td>
            <a data-toggle="tooltip" data-placement="top" title="Unable to regenerate since Elastic Search is not running.">
              <span class="fa fa-redo text-secondary"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="Unable to append newly-uploaded data to ElasticSearch since it not running.">
              <span class="fa fa-sync text-secondary"></span>
            </a>
          </td>
      <?php else: ?>
      <td id="status-<?= $row['source_id']; ?>">
      <?php if ($row['elastic_index'] == true): ?>

        <a data-toggle="tooltip" data-placement="top" title="Index '<?= $indexPrefix."_".$row['source_id']; ?>' exists.">
          <i class="fa fa-database text-success"></i>
        </a>
        <a title="Click to see index details." data-toggle="modal" data-target="#indexStatusModal" data-indexname="<?= $indexPrefix."_".$row['source_id']; ?>" data-elasticstatus="<?= $row['elastic_status'] ?>">
          <i class="fa fa-info-circle text-info"></i>
        </a>
      <?php else: ?>
        <a data-toggle="tooltip" data-placement="top" title="Index '<?= $indexPrefix."_".$row['source_id']; ?>' does not exist.">
          <i class="fa fa-database text-danger"></i>
        </a>
      <?php endif; ?>
      </td>
        <td id="action-<?= $row['source_id']; ?>">
          <?php if ($row['network_assigned']): ?>
            <?php if($row['elastic_lock']): ?>
            <a data-toggle="tooltip" data-placement="top" title="The source is locked as it appears that data is being appended to the source at the moment. Wait until the operation finishes and the source is unlocked.">
              <span class="fa fa-lock text-danger"></span>
            </a>
            <?php else: ?>
            <a onclick="regenElastic('<?php echo $row['source_id']; ?>', false);" id="update_<?php echo $row['name']; ?>" data-toggle="tooltip" data-placement="top" title="Click to regenerate this ElasticSearch Index">
              <span class="fa fa-redo text-info"></span>
            </a>
            <a onclick="regenElastic('<?php echo $row['source_id']; ?>',true);" data-toggle="tooltip" data-placement="top" title="Click to append newly-uploaded data to ElasticSearch (This does not affect data already present)">
              <span class="fa fa-sync text-warning"></span>
            </a>
            <?php endif; ?>
          <?php else: ?>
            <a data-toggle="tooltip" data-placement="top" title="Source is not assigned to a network. Please assign it to a network in network groups.">
              <span class="fa fa-exclamation-triangle text-warning"></span>
            </a>
          <?php endif; ?>
        </td>
      <?php endif; ?>

      </tr>
    <?php endforeach; ?>
  </tbody>
</table>


<div class="modal fade" id="indexStatusModal" tabindex="-1" role="dialog" aria-labelledby="indexStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="indexStatusModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <div id="index_details"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
