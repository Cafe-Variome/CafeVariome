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
<?php if ( ! $networks ): ?>
<div class="row">
	<div class="col">
		<p>You are currently not a member of any networks.</p>
	</div>
</div>
<?php else: ?>
<table class="table table-bordered table-striped table-hover" id="networkstable">
	<thead>
		<tr>
			<th>Network ID</th>
			<th>Network Name</th>
			<th>Installations</th>
			<th>Total Installation Count</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $networks as $network ):?>
		<tr>
			<td><?php echo $network->network_key; ?></td>
			<td><?php echo $network->network_name; ?></td>
			<td>
				<?php //foreach ( $network['installations'] as $network_key => $installation ): ?>
				<a href="<?php //echo $installation['installation_base_url'];?>"><?php //echo $installation['installation_base_url']; ?></a><br />
				<?php //endforeach; ?>
			</td>
			<td><?php //echo $network['count']; ?></td>
			<td>
			<a rel="popover" data-content="Set query results threshold value" data-original-title="Threshold" href="<?php echo base_url('network/edit_threshold') . '/' . $network->network_key . '/1'?>" >
				<i class="fa fa-lock"></i>
			</a>
			<?php if (isset($groups[$network->network_name])): ?>
			<a rel="popover" data-content="Add/Remove users for this network" data-original-title="Edit Network Users" href="<?php echo base_url('network/edit_user_network_groups') . '/' . $groups[$network->network_name] . '/1'?>" >
				<i class="fa fa-edit"></i>
			</a>
			<?php else: ?>
			<a rel="popover" data-content="This network originates on another installation and therefore you cant add users to Master Group. Please message administrator of that installation." data-original-title="Edit Network Users">
				<i class="fa fa-edit"></i>
			</a>
			<?php endif; ?>		
			<a href="<?php echo base_url('network/leave_network') . "/" . $network->network_key . "/" . $network->network_name; ?>" rel="popover" data-content="Click to leave the network. N.B. this action cannot be undone and you will need to request to join the network again. If you are the last member of the network then this network will be permanently deleted." data-original-title="Leave Network">
				<i class="fa fa-trash"></i>
			</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
				
<?php endif; ?>
<br />

<div class="row">
	<div class="col">
		<a href="<?php echo base_url() . "network/create_network"; ?>" class="btn btn-primary" ><i class="fa fa-file"></i> Create Network </a>
		<a href="<?php echo base_url() . "network/join_network"; ?>" class="btn btn-primary" ><i class="fa fa-sign-in-alt"></i> Join an Existing Network</a>
	</div>
</div>

<?= $this->endSection() ?>