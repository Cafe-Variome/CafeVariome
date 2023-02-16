<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
<hr>
<?php if ($statusMessage) : ?>
    <div class="row">
        <div class="col">
            <div class="alert alert-<?= $statusMessageType ?>">
                <?php echo $statusMessage ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<table class="table table-bordered table-striped table-hover" id="userstable" style="width:100%;">
<thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
			<th>Email</th>
			<th>Last Login</th>
            <th>Status</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td><?= $user->getID(); ?></td>
                <td><?= $user->first_name . " " . $user->last_name; ?></td>
                 <td><?= $user->email; ?></td>
                 <td><?= $user->last_login ? date("H:i:s d-m-Y T", $user->last_login) : 'This user has not logged in yet.' ; ?></td>
                <td><?= $user->active ? 'Active' : 'Inactive' ?></td>
                <td><?= $user->is_admin ? 'Admin' : 'User' ?></td>
                <td>
                    <a class="btn btn-sm btn-info bg-gradient-info" href="<?php echo base_url($controllerName . '/Details') . "/" . $user->getID(); ?>">
                        <i class="fa fa-eye">&nbsp;</i> View User
					</a>
                    <a class="btn btn-sm btn-warning bg-gradient-warning" href="<?php echo base_url($controllerName . '/Update') . "/" . $user->getID(); ?>">
                        <i class="fa fa-edit"></i> Edit User
					</a>
                    <a class="btn btn-sm btn-danger bg-gradient-danger" href="<?php echo base_url($controllerName . '/Delete') . "/" . $user->getID(); ?>">
                        <i class="fa fa-trash"></i> Delete User
					</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div class="form-group row">
    <div class="col">
        <a href="<?php echo base_url($controllerName . '/Create'); ?>" class="btn btn-success bg-gradient-success">
            <i class="fa fa-plus"></i> Create a User</a>
    </div>
</div>

<?= $this->endSection() ?>
