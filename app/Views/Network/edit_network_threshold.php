<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "network";?>">Networks</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<div class="form-group">
Set network threshold value: 
<input class="form-control" type="text" placeholder="" id="threshold" style="text-align: left;" value=<?php echo $network_threshold; ?>> 

</div>
<div class="form-group row">
    <div class="col">
        <button type="button" id="btn_save_threshold" class="btn btn-primary"><i class="fa fa-save"></i>  Save</button>
        <a href="<?php echo base_url() . "network"; ?>" class="btn btn-secondary" ><i class="fa fa-backward"></i> Go back</a>        
    </div>
</div>
<div class="form-group">
</div>

<input type="hidden" name="" value="<?php echo $network_key; ?>" id="threshold_network_key">
<?= $this->endSection() ?>