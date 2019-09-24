<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin/index";?>">Dashboard Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if (array_key_exists('error', $groups)): ?>
<div class="row">
    <div class="col">
        <div class="alert alert-info">
            <p>There are currently no groups for any of the networks you belong to, create a new group for a network below.</p>
        </div>
    </div>
</div>
<?php else: ?>
<table class="table table-bordered table-striped table-hover" id="networkgroups_table">
    <thead>
        <tr>
            <th>Group Name</th>
            <th>Group Description</th>
            <th>Network Name</th>
            <th>Group Type</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($groups as $group): ?>
        <tr>
            <td><?php echo $group['name']; ?></td>
            <td><?php echo $group['description']; ?></td>
            <td><?php echo $group['network_name']; ?></td>
            <td><?php echo $group['group_type']; ?></td>
            <td>
            <?php $isMaster = $group['group_type'] == "master"; ?>
            <?php if($isMaster): ?>
                <a data-toggle="tooltip" data-placement="top" title="Assign sources to network group" href="<?php echo base_url('network/edit_user_network_groups'). "/" . $group['id'] . '/1'; ?>" >
                    <i class="fa fa-database"></i>
                </a> 
            <?php else: ?>
                <a data-toggle="tooltip" data-placement="top" title="Assign sources to network group" href="<?php echo base_url('network/edit_user_network_groups'). "/" . $group['id']; ?>" >
                    <i class="fa fa-database"></i>
                </a>             
            <?php endif; ?>          
            <?php if ( $group['group_type'] == "master" ): ?>
                <i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Unable to delete master network group"></i>
                <!-- Unable to delete master network group -->
            <?php elseif ( $group['number_of_sources'] > 0 ): ?>
                <i class="fa fa-trash"data-toggle="tooltip" data-placement="top" title="Unable to delete group with sources assigned"></i>
                <!-- Unable to delete group with sources assigned -->
            <?php else: ?>
                <a data-toggle="tooltip" data-placement="top" title="Remove network group" href="<?php echo base_url('networkgroup/delete_networkgroup'). "/" . $group['id']; ?>" >
                    <i class="fa fa-trash"></i>
                </a>
            <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<div class="row">
    <div class="col">
        <a href="<?php echo base_url() . "networkgroup/create_networkgroup";?>" class="btn btn-primary" >
            <i class="fa fa-file"></i> Create new network group
        </a>
    </div>
</div>
	
<div class="span10 offset1 pagination-centered"><br /><p>Network groups can be assigned to sources within you installation. Users who belong to those groups in your network are allowed access to restrictedAccess records in sources across the network.</p></div>


<?= $this->endSection() ?>