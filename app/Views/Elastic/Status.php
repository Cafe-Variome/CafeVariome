<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "elastic";?>">Elastic Search</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if (isset($isRunning)): ?>
<div class="row">
  <div class="col">
    <a onclick="regenerateElasticSearchIndex();" class="btn btn-secondary" rel="popover" data-content="Click to regenerate the ElasticSearch index for all variants. This should be done after importing new data." data-original-title="Regenerate ElasticSearch Index"><i class="icon-list-alt"></i>  Regenerate ElasticSearch Index</a>
  </div>
</div>
<?php else: ?>
<div class="row">
  <div class="col">
    <button type="button" id="elasticon" class="btn disabled"></button>
  </div>
</div>
<div class="row" id="buttonswitch"></div>
<?php endif; ?>

<br/>
<table class="table table-bordered table-hover table-striped" id="index_table">
  <thead>
    <tr>
        <th>ElasticSearch Index Name</th>
        <th>Status</th>
        <th>Regenerate</th>
        <th>Update</th>
    </tr>
  </thead>
  <tbody id="index_grid">
    <?php foreach ($elastic_update as $row): ?> 
    <tr id="index_<?php echo $row['source_id']; ?>">
      <td><?php echo $host."_".$row['source_id']." (Source: ".$row['name'].")"; ?></td>
        <?php if ($row['elastic_status'] == 1): ?>
          <td style="background-color: lightblue;">
              <i class="fa fa-plus"></i>
              Update Possible
            <!-- Update Possible -->
          </td>                                   
        <?php else: ?>
          <td style="background-color: lightgreen;">
            <i class="fa fa-check"></i>
            Up to Date
          </td>                                    
      <?php endif; ?>

      <td>                                    
        <?php if ($row['elastic_status'] != 1): ?>       
          <a class="btn btn-secondary disabled" id="update_<?php echo $row['name']; ?>" data-toggle="tooltip" data-placement="top" title="Click to regenerate this ElasticSearch Index">
            <i class="fa fa-list"></i>  Regenerate <?php echo $host."_".$row['source_id']; ?> 
          </a>
          To force the Regenerate:
          <input class="check" title="Check this Box if you wish to force the update." id="update_<?php echo $row['name']; ?>_force" type="checkbox" value="Force?" />
        <?php else: ?>
          <a onclick="regenElastic('<?php echo $row['source_id']; ?>', false);" class="btn btn-secondary" id="update_<?php echo $row['name']; ?>" data-toggle="tooltip" data-placement="top" title="Click to regenerate this ElasticSearch Index">
            <i class="fa fa-list"></i>  Regenerate <?php echo $host."_".$row['source_id']; ?>
          </a>
        <?php endif; ?>                                                                    
      </td>
      <td>
        <a onclick="regenElastic('<?php echo $row['source_id']; ?>',true);" class="btn btn-secondary"  data-toggle="tooltip" data-placement="top" title="Click to append newly-uploaded data to ElasticSearch (This does not affect data already present)">
          <i class="fa fa-list"></i>  Update <?php echo $host."_".$row['source_id']; ?>
        </a>
      </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $this->endSection() ?>