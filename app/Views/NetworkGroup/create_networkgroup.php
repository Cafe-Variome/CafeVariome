<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<div class="container">
	<div class="row">  
		<div class="span6">  
			<ul class="breadcrumb">  
				<li>  
					<a href="<?php echo base_url() . "admin";?>">Dashboard Home</a> <span class="divider">></span>  
				</li>
				<li>
					<a href="<?php echo base_url() . "groups";?>">Groups</a> <span class="divider">></span>
				</li>
				<li class="active">Create Network Group</li>
			</ul>  
		</div>  
	</div>
	<div class="row-fluid">
		<div class="span8 offset3">
			<h1>Create Network Group</h1>
			
			<div id="infoMessage"><b><?php echo $message; ?></b></div>
			<?php if ( ! isset($networks)): ?>
			<hr>
			<p>You do not appear to be part of any networks so cannot create network groups. You should <a href="<?php echo base_url() . "networks";?>" >create or join a network</a>.</p>
			<?php else: ?>
			<p>Please enter the network group information below.</p>
			<?php echo form_open("groups/create_network_group"); ?>
			<p>
				Network: <br />
				<?php
					$options = array();
					foreach ($networks as $network) {
//						print_r($network);
						$options[$network['network_key']] = $network['network_name'];
					}
					echo form_dropdown('network', $options);
				?>
				<?php // echo form_input($network); ?>
			</p>
			<p>
				Group Name: <br />
				<?php echo form_input($group_name); ?>
			</p>

			<p>
				Description: <br />
				<?php echo form_input($desc); ?>
			</p>
			<p>
				Group Type: <br />
				<?php
					$options = array('source_display' => 'Source Display', 'count_display' => 'Count Display');
					echo form_dropdown('group_type', $options);
				?>
				<?php // echo form_input($network); ?>
			</p>

			<p><button type="submit" name="submit" class="btn btn-primary"><i class="icon-th icon-white"></i>  Create Group</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo base_url() . "groups";?>" class="btn" ><i class="icon-step-backward"></i> Go back</a></p>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>