<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if($message): ?>
<div class="alert alert-info">
    <?php echo $message; ?>
</div>
<?php endif ?>

<table class="table table-bordered table-striped table-hover" id="userstable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Institute</th>
            <th>Email</th>
            <th>Network Groups</th>
            <th>Status</th>
            <th>Remote</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr style="font-size:11px;">
            <td><?php echo $user['id']; ?></td>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['first_name']; ?></td>
            <td><?php echo $user['last_name']; ?></td>
            <td><?php echo $user['company']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td>
                <?php if ( isset($users_groups)): ?>
                    <?php if (array_key_exists($user['id'], $users_groups)): ?>
                    <?php foreach ($users_groups[$user['id']] as $group): ?>
                            <?php echo $group['group_description'] . " (Network:" . $group['network_name'] . ")"; ?><br />
                    <?php endforeach ?>
                    <?php endif; ?>
                <?php endif; ?>       
            </td>	
            <td><?php if ($user['active']) { echo 'Active'; } else { echo 'Inactive'; } ?></td>
            <td><?php if ($user['remote']) { echo 'Remote User'; } else { echo 'Local User'; } ?></td>
            <td>
                <a data-toggle="tooltip" data-placement="top" title="Edit User" href="<?php echo base_url('user/edit_user'). "/" . $user['id']; ?>" >
                    <i class="fa fa-edit"></i>
                </a>
                <a data-toggle="tooltip" data-placement="top" title="Delete User" href="<?php echo base_url('user/delete_user'). "/" . $user['id']; ?>" >
                    <i class="fa fa-trash"></i>
                </a>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="form-group row">
    <div class="col">
        <a href="<?php echo base_url() . "user/create_user";?>" class="btn btn-primary" >
            <i class="icon-user icon-white"></i> Create new user</a>
        <a href="<?php echo base_url() . "admin";?>" class="btn btn-secondary" >
            <i class="fa fa-home"></i> Admin Dashboard</a>
    </div>
</div>


<?= $this->endSection() ?>