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
<table class="table table-bordered table-hover table-striped" id="index_table">
  <thead>
    <tr>
        <th>ElasticSearch Index Name</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
  </thead>
  <tbody id="index_grid">
    <?php foreach ($elastic_update as $row): ?> 
    <tr id="index_<?php echo $row['source_id']; ?>">
      <td><?php echo $host."_".$row['source_id']." (Source: ".$row['name'].")"; ?></td>
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
        <?php if ($row['elastic_index'] == true): ?>
          <td style="background-color: lightgreen;">
            <i class="fa fa-check"></i>
            Up to Date
          </td>                                  
        <?php else: ?>
          <td style="background-color: lightblue;">
              <i class="fa fa-plus"></i>
              Update Possible
            <!-- Update Possible -->
          </td>                                               
      <?php endif; ?>
        <td>   
          <?php if ($row['network_assigned']): ?>                                 
            <a onclick="regenElastic('<?php echo $row['source_id']; ?>', false);" id="update_<?php echo $row['name']; ?>" data-toggle="tooltip" data-placement="top" title="Click to regenerate this ElasticSearch Index">
              <span class="fa fa-redo text-info"></span>
            </a>
            <a onclick="regenElastic('<?php echo $row['source_id']; ?>',true);" data-toggle="tooltip" data-placement="top" title="Click to append newly-uploaded data to ElasticSearch (This does not affect data already present)">
              <span class="fa fa-sync text-warning"></span>
            </a>
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
<?= $this->endSection() ?>