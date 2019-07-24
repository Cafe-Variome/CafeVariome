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
					<a href="<?php echo base_url() . "networks";?>">Networks</a> <span class="divider">></span>
				</li>
				<li class="active">Join Network</li>
			</ul>  
		</div>  
	</div>
	<div class="row-fluid">
		<div class="span9 offset2 pagination-centered">
			<div class="well">
				<h2>Join Network</h2>
				<?php if ( isset($networks) ): ?>
				<?php if (array_key_exists('error', $networks) ): ?>
				<p>There are no networks available for you to join (or you are already a member of all existing networks).</p>
				<p>Go to the <a href="<?php echo base_url() . "networks/create_network"; ?>">create networks page</a> if you would like to start a new network.</p>
				<?php else: ?>
				<?php echo form_open("networks/join_network", array('name' => 'joinNetwork')); ?>
				<p>Select a network you wish to join.<br/>
					<?php $network_count = count($networks) + 1; ?>
					<select size="<?php echo $network_count; ?>" name="networks" id="networks" >
					<?php foreach ($networks as $network) : ?>
						<option value="<?php echo $network['network_key']; ?>" selected="selected"><?php echo $network['network_name']; ?></option>
					<?php endforeach; ?>
					</select>
				</p>
				
				<p>
					Justification: <br />
					<?php echo form_textarea($justification); ?>
				</p>
                                
				<p><button type="submit" onclick="join_network();" class="btn btn-primary"><i class="icon-file icon-white"></i>  Join Network</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo base_url() . "networks"; ?>" class="btn" ><i class="icon-step-backward"></i> Go back</a></p>
				<?php endif; ?>
				<?php else: ?>
				<p>There was a problem finding available networks to join, please contact admin@cafevariome.org if the problem persists</p>
				<?php endif; ?>
				<?php 
				if (isset($result)) {
					echo "<p>Network request received successfully.</p>";
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>
