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
        <h4>This installation is not part of any networks</h4>
    <div>
</div>
<?php else: ?>
<div class="row">
    <div class="col">
        <h4>Select the network you would like to search</h4>
    <div>
</div>

<form method="post">
    <div class="form-group">
        <select class="form-control" name="selectNetwork" id="selectNetwork">
            <option></option>
            <?php foreach ($networks as $network) : ?>
                <option value="<?php echo $network->network_key; ?>"><?php echo $network->network_name; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" id="network_select" onclick="networkSelect();" type="button">Submit</button>
    </div>
</form>
<?php endif ?>
    
<?= $this->endSection() ?>
