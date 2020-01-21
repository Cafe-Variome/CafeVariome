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
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
	<hr>
<?php endif; ?>
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
			<th>Total Installations</th>
			<th>Action</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $networks as $network ):?>
		<tr>
			<td><?php echo $network->network_key; ?></td>
			<td><?php echo $network->network_name; ?></td>
			<td>
				<?php foreach ( $network->installations as $installation ): ?>
				<a href="<?php echo $installation->base_url;?>"><?php echo $installation->name; ?></a><br />
				<?php endforeach; ?>
			</td>
			<td><?php echo $network->installations_count; ?></td>
			<td>
				<a href="<?php echo base_url('network/Update_Threshold' . '/' . $network->network_key . '/1') ?>" data-toggle="tooltip" data-placement="top" title="Edit Network Threshold">
					<span class="fa fa-tachometer-alt text-warning"></i>
				</a>
			<?php if (isset($groups[$network->network_name])): ?>
				<a href="<?php echo base_url('network/Update_Users' . '/' . $groups[$network->network_name] . '/1') ?>" data-toggle="tooltip" data-placement="top" title="Edit Users in Network">
					<span class="fa fa-user text-info"></i>
				</a>
			<?php else: ?>
				<a data-toggle="tooltip" data-placement="top" title="Users of this network cannot be edited on this installation.">
					<span class="fa fa-user text-default"></i>
				</a>
			<?php endif; ?>		
				<a href="<?php echo base_url('network/Leave' . "/" . $network->network_key . "/" . $network->network_name); ?>" data-toggle="tooltip" data-placement="top" title="Leave Network">
					<i class="fa fa-trash text-danger"></i>
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
		<a href="<?php echo base_url() . "Network/Create"; ?>" class="btn btn-primary" ><i class="fa fa-file"></i> Create Network </a>
		<a href="<?php echo base_url() . "Network/Join"; ?>" class="btn btn-primary" ><i class="fa fa-sign-in-alt"></i> Join an Existing Network</a>
	</div>
</div>

<?= $this->endSection() ?>