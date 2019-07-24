<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<div class="container">
	<div class="row">  
		<div class="span6">  
			<ul class="breadcrumb">
				<li>  
					<a href="<?php echo base_url() . "admin";?>">Dashboard Home</a> <span class="divider">></span>  
				</li>
				<li class="active">Networks</li>
			</ul>  
		</div>  
	</div>
	
	<div class="row-fluid">
		<div class="span12">
			<div class="pagination-centered" >
				<div class="well">
					<h3>My Networks</h3>
					<p>
						<a href="<?php echo base_url() . "network/create_network"; ?>" class="btn btn-primary" > Create a new network</a>
						<a href="<?php echo base_url() . "network/join_network"; ?>" class="btn btn-primary" > Join an existing network</a>
					</p>
					<hr>
					<?php if ( ! $networks ): ?>
					<p>You are currently not a member of any networks.</p>
					<?php else: ?>
					<table class="table table-bordered table-striped table-hover general">
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
							<?php foreach ( $networks as $network ):  
							// error_log(print_r($network, 1));?>
							<tr>
								<td><?php echo $network['network_id']; ?></td>
								<td><?php echo $network['network_name']; ?></td>
								<td>
									<?php foreach ( $network['installations'] as $network_key => $installation ): ?>
									<a href="<?php echo $installation['installation_base_url'];?>"><?php echo $installation['installation_base_url']; ?></a><br />
									<?php endforeach; ?>
								</td>
								<td><?php echo $network['count']; ?></td>
								<td>
								<a rel="popover" data-content="Set query results threshold value" data-original-title="Threshold" 
									href="<?php echo base_url('networks/edit_threshold') . '/' . $network['network_key'] . '/1'?>" ><i class=" icon-lock"></i></a>
									&nbsp;&nbsp;
									<?php if (isset($groups[$network['network_name']])): ?>
										<a rel="popover" data-content="Add/Remove users for this network" data-original-title="Edit Network Users" 
										href="<?php echo base_url('auth_federated/edit_user_network_groups') . '/' . $groups[$network['network_name']] . '/1'?>" ><i class="icon-edit"></i></a>
									<?php else: ?>
										<a rel="popover" data-content="This network originates on another installation and therefore you cant add users to Master Group. Please message administrator of that installation." data-original-title="Edit Network Users" 
										 ><i class="icon-edit"></i></a>
									<?php endif; ?>
									&nbsp;&nbsp;
									
									<a href="<?php echo base_url('networks/leave_network') . "/" . $network['network_key'] . "/" . $network['network_name']; ?>" rel="popover" data-content="Click to leave the network. N.B. this action cannot be undone and you will need to request to join the network again. If you are the last member of the network then this network will be permanently deleted." data-original-title="Leave Network"></i><i class="icon-remove"></i></a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				
					<br />
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<br />
</div>
<?= $this->endSection() ?>