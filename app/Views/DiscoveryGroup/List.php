<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
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
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update'). "/" . $discoveryGroup->getID(); ?>" >
					<i class="fa fa-edit"></i> Edit Discovery Group
				</a>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?= base_url($controllerName . '/Details'). "/" . $discoveryGroup->getID(); ?>" >
					<i class="fa fa-eye"></i> View Discovery Group
				</a>
                <a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName.'/Delete'). "/" . $discoveryGroup->getID(); ?>" >
                    <i class="fa fa-trash"></i> Delete Discovery group
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
