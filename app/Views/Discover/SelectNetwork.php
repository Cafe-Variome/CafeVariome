<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>

<?php if(count($networks) == 0): ?>
<div class="row">
    <div class="col">
        <h4>
			Either this installation or your user account is not in any network.
			<a href= <?php echo base_url("/Network/Create")?>>Create</a> or <a href= <?php echo base_url("/Network/Join")?>> join </a>
			a network to continue. </h4>
    </div>
</div>
<?php else: ?>
<div class="row mb-3">
    <div class="col">
        <h4>Select the network you would like to search:</h4>
    </div>
</div>
<br>
<div class="row">
	<div class="col pr-5 pl-5">
		<?php for($i = 0; $i < count($networks); $i++): ?>
			<?php if($i == 0 ): ?>
				<div class="row">
			<?php elseif(($i) % 4 == 0): ?>
				</div><div class="row mt-3">
			<?php endif ?>
			<div class="col-3">
				<a class="btn btn-lg btn-info font-weight-bolder" href="<?= base_url($controllerName . '/QueryBuilder/' . $networks[$i]->network_id ) ?>"><i class="fas fa-fw fa-network-wired"></i> <?= $networks[$i]->network_name ?></a>
			</div>
			<?php if($i + 1 == count($networks)): ?>
				</div>
			<?php endif ?>
		<?php endfor; ?>
	</div>
</div>

<?php endif ?>

<?= $this->endSection() ?>
