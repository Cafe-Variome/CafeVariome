<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
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
                <a data-toggle="tooltip" data-placement="top" title="Assign sources to network group" href="<?= base_url('NetworkGroup/Update_Users'). "/" . $group['id'] . '/1'; ?>" >
                    <i class="fa fa-database text-warning"></i>
                </a> 
            <?php else: ?>
                <a data-toggle="tooltip" data-placement="top" title="Assign sources to network group" href="<?= base_url('NetworkGroup/Update_Users'). "/" . $group['id']; ?>" >
                    <i class="fa fa-database text-warning"></i>
                </a>             
            <?php endif; ?>          
            <?php if ( $group['group_type'] == "master" ): ?>
                <i class="fa fa-trash" data-toggle="tooltip" data-placement="top" title="Unable to delete master network group"></i>
                <!-- Unable to delete master network group -->
            <?php elseif ( $group['number_of_sources'] > 0 ): ?>
                <i class="fa fa-trash text-danger" data-toggle="tooltip" data-placement="top" title="Unable to delete group with sources assigned"></i>
                <!-- Unable to delete group with sources assigned -->
            <?php else: ?>
                <a data-toggle="tooltip" data-placement="top" title="Remove network group" href="<?= base_url($controllerName.'/Delete'). "/" . $group['id']; ?>" >
                    <i class="fa fa-trash text-danger"></i>
                </a>
            <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col">
        <a href="<?php echo base_url($controllerName) .'/Create';?>" class="btn btn-primary" >
            <i class="fa fa-file"></i> Create new network group
        </a>
    </div>
</div>
<?= $this->endSection() ?>