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
        <table class="table table-bordered table-striped table-hover" id="userdetailstable" style="width:40%;">
            <tr>
                <th>ID</th>
                <td><?php echo $user->getID(); ?></td>
            </tr>
			<tr>
				<th>Full Name</th>
				<td><?php echo $user->first_name . " " . $user->last_name; ?></td>
            </tr>
			<tr>
				<th>Email</th>
				<td><?php echo $user->email; ?></td>
            </tr>
			<tr>
				<th>Company</th>
				<td><?php echo $user->company; ?></td>
            </tr>
<!--			<tr>-->
<!--				<th>Phone</th>-->
<!--				<td>--><?php //echo $user->phone; ?><!--</td>-->
<!--            </tr>-->
			<tr>
            <th>Remote</th>
				<td><?= $user->remote ? 'Remote User' : 'Local User' ?></td>
            </tr>
			<tr>
				<th>Last Login</th>
				<td><?= $user->last_login ? date("H:i:s d-m-Y T", $user->last_login) : 'This user has not logged in yet.' ; ?></td>
            </tr>
			<tr>
				<th>Created On</th>
				<td><?= date("H:i:s d-m-Y T", $user->last_login); ?></td>
            </tr>
			<tr>
				<th>Role</th>
				<td><?= $user->is_admin ? 'Admin' : 'User' ?></td>
            </tr>
			<tr>
				<th>IP Address</th>
				<td><?php echo $user->ip_address; ?></td>
            </tr>
			<tr>
            	<th>Status</th>
				<td><?= $user->active ? 'Active' : 'Inactive' ?></td>
            </tr>
        </table>
        <hr />
        <a href="<?= base_url($controllerName . '/Update') . "/" . $user->getID(); ?>" class="btn btn-warning bg-gradient-warning">
            <i class="fa fa-edit"></i>&nbsp;Edit
		</a>
		<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fas fa-fw fa-user"></i> View Users
		</a>
		<hr />
    </div>
</div>

<?= $this->endSection() ?>
