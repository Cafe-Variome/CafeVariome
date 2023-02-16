<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
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
            <td><?php echo $networkRequest->email; ?></td>
            <td><?php echo $networkRequest->network_key; ?></td>
            <td><?php echo $networkRequest->url; ?></td>
            <td><?php echo $networkRequest->justification; ?></td>
            <td><?php echo $networkRequest->ip; ?></td>
            <td>
            <?php if($networkRequest->status == NETWORKREQUEST_ACCEPTED): ?>
                <span class="badge bg-success">Accepted</span>
            <?php elseif($networkRequest->status == NETWORKREQUEST_REJECTED): ?>
                <span class="bag bg-danger">Rejected</span>
            <?php elseif($networkRequest->status == NETWORKREQUEST_PENDING): ?>
                <span class="badge bg-info">Pending</span>
            <?php endif; ?>
            </td>
            <td>
            <?php if($networkRequest->status == NETWORKREQUEST_PENDING): ?>
                <a class="btn btn-sm btn-success bg-gradient-success" href="<?= base_url($controllerName . '/Accept/'. $networkRequest->getID()) ?>">
					<span class="fa fa-check"></span> Accept
				</a>
                <a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName . '/Reject/'. $networkRequest->getID()) ?>">
					<span class="fa fa-times"></span> Reject
				</a>
            <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?= $this->endSection() ?>
