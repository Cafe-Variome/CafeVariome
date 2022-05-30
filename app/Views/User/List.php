<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col">
        <h2><?= $title ?></h2>
    </div>
</div>
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
            <th>Network Groups</th>
            <th>Status</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user) : ?>
            <tr>
                <td style="height:80px;"><?= $user->getID(); ?></td>
                <td><?= $user->first_name . " " . $user->last_name; ?></td>
                 <td><?= $user->email; ?></td>
                 <td><?= $user->last_login ? date("H:i:s d-m-Y T", $user->last_login) : 'This user has not logged in yet.' ; ?></td>
                <td>
                    <?php if (isset($users_groups)) : ?>
                        <?php if (array_key_exists($user->getID(), $users_groups)) : ?>
                            <?php foreach ($users_groups[$user->getID()] as $group) : ?>
                                <?php echo "<i>'" . $group['group_description'] . "' Network Group</i> (Network: " . $group['network_name'] . ");<br/>" ?>
                                <?php endforeach ?>
                        <?php else : echo "None"; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?= $user->active ? 'Active' : 'Inactive' ?></td>
                <td><?= $user->is_admin ? 'Admin' : 'User' ?></td>
                <td>
                    <a data-toggle="tooltip" data-placement="top" title="View User" href="<?php echo base_url($controllerName . '/Details') . "/" . $user->getID(); ?>">
                        <i class="fa fa-eye text-info">&nbsp;&nbsp;</i></a>
                    <a data-toggle="tooltip" data-placement="top" title="Edit User" href="<?php echo base_url($controllerName . '/Update') . "/" . $user->getID(); ?>">
                        <i class="fa fa-edit text-warning">&nbsp;&nbsp;</i></a>
                    <a data-toggle="tooltip" data-placement="top" title="Delete User" href="<?php echo base_url($controllerName . '/Delete') . "/" . $user->getID(); ?>">
                        <i class="fa fa-trash text-danger">&nbsp;&nbsp;</i></a>
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
