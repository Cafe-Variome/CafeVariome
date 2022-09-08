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
<table class="table table-bordered table-striped table-hover" id="discoverygroupstable">
    <thead>
        <tr>
            <th>Name</th>
            <th>Network Name</th>
            <th>Policy</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($discoveryGroups as $discoveryGroup): ?>
        <tr>
            <td><?php echo $discoveryGroup->name; ?></td>
            <td><?php echo $discoveryGroup->network_name; ?></td>
            <td><?php echo $discoveryGroup->policy; ?></td>
            <td>
				<a data-toggle="tooltip" data-placement="top" title="Edit Discovery Group" href="<?= base_url($controllerName . '/Update'). "/" . $discoveryGroup->getID(); ?>" >
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a data-toggle="tooltip" data-placement="top" title="View Discovery Group" href="<?= base_url($controllerName . '/Details'). "/" . $discoveryGroup->getID(); ?>" >
					<i class="fa fa-eye text-info"></i>
				</a>
                <a data-toggle="tooltip" data-placement="top" title="Remove network group" href="<?= base_url($controllerName.'/Delete'). "/" . $discoveryGroup->getID(); ?>" >
                    <i class="fa fa-trash text-danger"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col">
        <a href="<?php echo base_url($controllerName) .'/Create';?>" class="btn btn-success bg-gradient-success" >
            <i class="fa fa-plus"></i> Create a Discovery Group
        </a>
    </div>
</div>
<?= $this->endSection() ?>
