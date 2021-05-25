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
            </tr>
                <th>Subject ID Location</th>
                <td>
                    <?php if($pipeline['subject_id_location'] == 0): ?>
                        Attribute in File
                    <?php elseif($pipeline['subject_id_location'] == 1): ?>
                        File Name
                    <?php endif; ?>
                </td>
            </tr>
            </tr>
                <th>Subject ID Attribute Name</th>
                <td>
                    <?= $pipeline['subject_id_attribute_name'] ?>
                </td>
            </tr>
            <tr>
                <th>Grouping</th>
                <td>
                    <?php if($pipeline['grouping'] == 0): ?>
                        Group Individually
                    <?php elseif($pipeline['grouping'] == 1): ?>
                        Custom
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Group Columns</th>
                <td>
                    <?= $pipeline['group_columns'] ?>
                </td>
            </tr>
            <tr>
                <th>HPO Attribute Name</th>
                <td>
                    <?= $pipeline['hpo_attribute_name'] ?>
                </td>
            </tr>
            <tr>
                <th>Negated HPO Attribute Name</th>
                <td>
                    <?= $pipeline['negated_hpo_attribute_name'] ?>
                </td>
            </tr>
            <tr>
                <th>ORPHA Attribute Name</th>
                <td>
                    <?= $pipeline['orpha_attribute_name'] ?>
                </td>
            </tr>
            <tr>
                <th>Internal Delimiter</th>
                <td>
                    <?= $pipeline['internal_delimiter'] ?>
                </td>
            </tr>
        </table>
        <hr />
        <a href="<?php echo base_url($controllerName . '/Update') . "/" . $pipeline['id']; ?>" class="btn btn-warning">
            <i class="fa fa-edit"></i>&nbsp;Edit</a>
        <a href="<?php echo base_url($controllerName . '/List'); ?>" class="btn btn-secondary"><i class="fa fa-backward"></i> Go back</a>
        <hr />
    </div>
</div>

<?= $this->endSection() ?>