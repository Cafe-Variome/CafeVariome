<?= $this->extend('layout\master') ?>
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

<?php foreach ($settings as $sk => $sval): ?>
<div class="form-group row">
    <div class="col-4">
        <?= $sk ?>
    </div>
    <div class="col-8">
        <input type="text" class="form-control" value="<?= $sval ?>">
    </div>
</div>
<?php endforeach; ?>
<?= $this->endSection() ?>