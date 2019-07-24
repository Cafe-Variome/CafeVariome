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
				<li class="active">Create Network</li>
			</ul>  
		</div>  
	</div>
	<div class="row-fluid">
		<div class="span9 offset2 pagination-centered">
			<div class="well">
				<h2>Create Network</h2>
				<p>Please enter the network information below.</p>
				<b><strong><?= $validation->listErrors() ?></strong></b>
				
				<?php echo form_open("network/create_network", array('name' => 'createNetwork')); ?>
				<p>
					Network Name:<br />
					<?php echo form_input($name); ?>
					<br />
					<small>(no spaces allowed but underscores and dashes are accepted, <br />uppercase characters will be converted to lowercase)</small>
				</p>
				<p><button type="submit" name="submit" class="btn btn-primary"><i class="icon-file icon-white"></i>  Create Network</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo base_url() . "networks"; ?>" class="btn" ><i class="icon-step-backward"></i> Go back</a></p>
				<br />
				<?php if($message): ?>
				<div class="row">
				<div class="col">
					<div class="alert alert-info">
					<?php echo $message ?>
					</div>
				</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<?= $this->endSection() ?>