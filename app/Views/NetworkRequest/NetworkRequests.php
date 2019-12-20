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

<table class="table table-bordered table-striped table-hover" id="networkrequests_table">
    <thead>
        <tr>
            <th>Email</th>
            <th>Network Key</th>
            <th>Installation URL</th>
            <th>Justification</th>
            <th>IP</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($networkRequests as $networkRequest): ?>
        <tr>
            <td><?php echo $networkRequest['email']; ?></td>
            <td><?php echo $networkRequest['network_key']; ?></td>
            <td><?php echo $networkRequest['url']; ?></td>
            <td><?php echo $networkRequest['justification']; ?></td>
            <td><?php echo $networkRequest['ip']; ?></td>
            <td><?php echo $networkRequest['status']; ?></td>
            <td>
            <a href="networkrequest/acceptrequest" data-toggle="tooltip" data-placement="top" title="Accept Request"><span class="fa fa-check text-success"></span></a>
            <a href="networkrequest/denyrequest" data-toggle="tooltip" data-placement="top" title="Deny Request"><span class="fa fa-times text-danger"></span></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?= $this->endSection() ?>