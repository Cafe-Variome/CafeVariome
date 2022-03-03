<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
        <div class="alert alert-<?= $statusMessageType ?>">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-auto">
        <table class="table table-bordered table-striped table-hover" id="datapipelinedetailstable">
            <tr>
                <th>ID</th>
                <td><?= $pipeline['id']; ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?= $pipeline['name'] ?></td>
            </tr>
            <tr>
                <th>Subject ID Location</th>
                <td>
                    <?= $pipeline['subject_id_location_text']?>
                </td>
            </tr>
            </tr>
                <th>Subject ID Attribute Name</th>
                <td>
                    <?= $pipeline['subject_id_attribute_name'] ?>
                </td>
            </tr>
            <?php if($pipeline['subject_id_location'] == SUBJECT_ID_PER_BATCH_OF_RECORDS): ?>
			</tr>
			<th>Subject ID Assignment Batch Size</th>
			<td>
				<?= $pipeline['subject_id_assignment_batch_size'] ?>
			</td>
			</tr>
			</tr>
			<th>Subject ID Prefix</th>
			<td>
				<?= $pipeline['subject_id_prefix'] ?>
			</td>
			</tr>
			<?php endif; ?>
			<tr>
				<th>Expansion Columns</th>
				<td>
					<?= $pipeline['expansion_columns'] ?>
				</td>
			</tr>
			<tr>
				<th>Expansion Policy</th>
				<td>
					<?= $pipeline['expansion_policy'] ?>
				</td>
			</tr>
			<tr>
				<th>Expansion Attribute Name</th>
				<td>
					<?= $pipeline['expansion_attribute_name'] ?>
				</td>
			</tr>
            <tr>
                <th>Grouping</th>
                <td>
                    <?= $pipeline['grouping'] ?>
                </td>
            </tr>
            <tr>
                <th>Group Columns</th>
                <td>
                    <?= $pipeline['group_columns'] ?>
                </td>
            </tr>
            <tr>
                <th>Internal Delimiter</th>
                <td>
                    <?= $pipeline['internal_delimiter'] ?>
                </td>
            </tr>
        </table>

    </div>
</div>
<hr>
<div class="row mb-5">
	<div class="col">
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-grip-lines-vertical"></i> View Pipelines
		</a>
		<a href="<?= base_url($controllerName . '/Update') . "/" . $pipeline['id']; ?>" class="btn btn-warning bg-gradient-warning">
			<i class="fa fa-edit"></i>&nbsp;Edit Pipeline
		</a>
		<a href="<?= base_url($controllerName . '/Delete') . "/" . $pipeline['id']; ?>" class="btn btn-danger bg-gradient-danger">
			<i class="fa fa-trash"></i>&nbsp;Delete Pipeline
		</a>
	</div>
</div>
<?= $this->endSection() ?>
