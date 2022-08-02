<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col">
        <h2><?= $title ?></h2>
    </div>
</div>
<hr>
<?php if ($statusMessage) : ?>
    <div class="row">
        <div class="col">
            <div class="alert alert-<?= $statusMessageType ?>">
                <?php echo $statusMessage ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<table class="table table-bordered table-striped table-hover" id="sourcestable">
	<thead>
		<tr>
			<th>Name</th>
			<th>Record Count</th>
			<th>Assigned Group(s)</th>
			<th>Status</th>
			<th>Quick Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($sources as $source): ?>
			<td>
				<?php echo $source->name; ?>
				<br>
				<span class="text-muted" style="font-size:10px;">Displayed as (<?php echo $source->display_name; ?>)</span>
			</td>
			<td><?= $source->record_count; ?></td>
			<td>
				<?php
					if ( isset($source_network_groups)):
						if (array_key_exists($source->getID(), $source_network_groups)):
							foreach ($source_network_groups[$source->getID()] as $group):
								echo $group['description'] . " (Network:" . $group['network_name'] . ")<br />";
							endforeach;
						else:
							echo "No groups assigned";
						endif;
					else:
						echo "No groups assigned";
					endif;
				?>
			</td>
			<td>
				<?= \App\Libraries\CafeVariome\Helpers\UI\SourceHelper::getSourceStatus($source->status) ?>
			</td>
			<td>
				<a class="btn btn-primary bg-gradient-primary font-weight-bold" href="<?= base_url('DataFile/List/' . $source->getID()) ?>">
					<i class="fa fa-file"></i> Data Files
				</a>
				<a class="btn btn-info bg-gradient-info font-weight-bold" href="<?= base_url('Attribute/List/' . $source->getID()) ?>">
					<i class="fa fa-database"></i> Data Attributes and Values
				</a>
				<button class="btn btn-success bg-gradient-success font-weight-bold" data-toggle="modal" data-target="#indicesModal" data-id="<?= $source->getID() ?>">
					<i class="fa fa-search"></i> Indices
				</button>
				<a class="btn btn-warning bg-gradient-warning font-weight-bold" href="<?= base_url($controllerName . '/Update/' . $source->getID()) ?>">
					<i class="fa fa-edit"></i> Edit
				</a>
				<a class="btn btn-danger bg-gradient-danger font-weight-bold" href="<?= base_url($controllerName . '/Delete/' . $source->getID()) ?>">
					<i class="fa fa-trash"></i> Delete
				</a>
			</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="row">
	<div class="col">
		<a href="<?php echo base_url($controllerName . '/Create') ?>" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i> Create a Source
		</a>
	</div>
</div>

<!-- INDICES MODAL -->
<div id="indicesModal" class="modal fade" style="justify-content: center;align-items:center" tabindex="-1" role="dialog" aria-labelledby="indicesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="indicesModalTitle">View Indices</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
				<div class="row">
					<div class="col">
						<a id="ESIndex" class="btn btn-info bg-gradient-info font-weight-bold" href="">
							<i class="fa fa-search"></i> Elasticsearch Index
						</a>
					</div>
					<div class="col">
						<a id="NeoIndex" class="btn btn-warning bg-gradient-warning font-weight-bold" href="">
							<i class="fa fa-project-diagram"></i> Neo4J Index
						</a>
					</div>
					<div class="col">
						<a id="UIIndex" class="btn btn-secondary bg-gradient-secondary font-weight-bold" href="">
							<i class="fa fa-desktop"></i> User Interface Index
						</a>
					</div>
				</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
