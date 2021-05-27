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
<table class="table table-bordered table-striped table-hover" id="sourcestable">
	<thead>
		<tr>
			<th>Name</th>
			<th>Description</th>
			<th>Record Count</th>
			<th>Assigned Group(s)</th>
			<th>Action</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
		<?php $c = 0; ?>
		<?php foreach ($sources as $source): ?>
		<?php $c++; ?>
			<td><?php echo $source['name']; ?></td>
			<td><?php echo $source['description']; ?></td>
			<td><?= $source['record_count']; ?></td>
			<td>
				<?php
					if ( isset($source_network_groups)):
						if (array_key_exists($source['source_id'], $source_network_groups)):
							foreach ($source_network_groups[$source['source_id']] as $group):
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
				<a data-toggle="modal" data-target="#addVariantsModal" data-id="<?= $source['source_id'] ?>" data-srcname="<?= $source['name']; ?>" data-placement="top" title="Upload Data Files" id="ImportRecordsBtn">
					<i class="fa fa-upload text-success"></i>
				</a>
				<a href="<?php echo base_url('Upload/Import'). "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="Import Files">
					<i class="fa fa-file-import text-primary"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Update'). "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="Edit Source">
					<i class="fa fa-edit text-warning"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Status'). "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="Source File Status">
					<i class="fa fa-info-circle text-primary"></i>
				</a>
				<a href="<?php echo base_url($controllerName. '/Data'). "/" . $source['source_id']; ?>" data-toggle="tooltip" data-placement="top" title="Data Attributes and Values">
					<i class="fa fa-database text-info"></i>
				</a>
				<a href="<?php echo base_url($controllerName.'/Delete'). "/" . $source['source_id'] . "/" . $source['name']; ?>" data-toggle="tooltip" data-placement="top" title="Delete Source">
					<i class="fa fa-trash text-danger"></i>
				</a>
			</td>
			<td>
			<?php if ( $source['status'] == "online" ): ?>
				Online
			<?php elseif ( $source['status'] == "offline" ): ?>
				Offline
			<?php endif; ?>
			</td>
		</tr>		
		<?php endforeach; ?>
	</tbody>
</table>

<div id="sourceDisplay"></div>

<br/>

<div class="row">
	<div class="col">
		<a href="<?php echo base_url($controllerName.'/Create') ?>" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-file"></i>  Create Source
		</a>
	</div>
</div>

<br/>

<div id="addVariantsModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addVariantsModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" align="center" id="addVariantsModalTitle"></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row mb-2">
					<div class="col">
						<a id="bulkImport" class="btn btn-small btn-primary">
							<i class="fa fa-file-upload"></i> Upload Spreadsheet Files
						</a>
					</div>				
				</div>
				<div class="row mb-2">
					<div class="col">
						<a id="phenoPacketsImport" class="btn btn-small btn-primary">
							<i class="fa fa-file-upload"></i> Upload PhenoPacket Files
						</a>
					</div>		
				</div>	
				<div class="row mb-2">
					<div class="col">
						<a id="VCFImport" class="btn btn-small btn-primary">
							<i class="fa fa-file-upload"></i> Upload VCF Files
						</a>
					</div>		
				</div>	
				<!-- <div class="row">
					<div class="col">
						<a id="UniversalImport" class="btn btn-small btn-primary">
							<i class="fa fa-plus"></i> Universal Import
						</a>
					</div>		
				</div>	
				<div class="row">
					<div class="col">
						<p>
							<i>Import any files</i>
						</p>
					</div>
				</div>	 -->
			</div>
			<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>		
		</div>
	</div>

</div>

<?= $this->endSection() ?>