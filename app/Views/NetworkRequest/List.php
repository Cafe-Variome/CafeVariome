<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

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
            <td>
            <?php if($networkRequest['status'] == '1'): ?>
                <span class="fa fa-check text-success" data-toggle="tooltip" data-placement="top" title="Accepted"></span>
            <?php elseif($networkRequest['status'] == '0'): ?>
                <span class="fa fa-times text-danger" data-toggle="tooltip" data-placement="top" title="Denied"></span>
            <?php elseif($networkRequest['status'] == '-1'): ?>
                <span class="fa fa-clock text-info" data-toggle="tooltip" data-placement="top" title="Pending..."></span>
            <?php endif; ?>
            </td>
            <td>
            <?php if($networkRequest['status'] == '-1'): ?>
                <a href="<?= base_url($controllerName . '/acceptrequest/'.$networkRequest['id']) ?>" data-toggle="tooltip" data-placement="top" title="Accept Request"><span class="fa fa-check text-success"></span></a>
                <a href="<?= base_url($controllerName . '/denyrequest/'.$networkRequest['id']) ?>" data-toggle="tooltip" data-placement="top" title="Deny Request"><span class="fa fa-times text-danger"></span></a>
            <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?= $this->endSection() ?>