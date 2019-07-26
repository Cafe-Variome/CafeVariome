<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sources</li>
  </ol>
</nav>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
	
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
		<?php if ( $source['type'] == "api"): ?>
		<tr id="<?php echo $source['source_id']; ?>" style="background-color:#57FEFF">
		<?php else: ?>
		<tr id="<?php echo $source['source_id']; ?>">	
		<?php endif; ?>
			<td><?php echo $source['name']; ?></td>
			<td><?php echo $source['description']; ?></td>
			<td>
				<?php if ( $source['type'] != "api" && $source['type'] != "central" ): ?>
					<?php
					if ( isset($variant_counts[$source['source_id']]) ):
						echo $variant_counts[$source['source_id']];
					else:?>
						<button data-toggle="modal" data-target="#addVariantsModal" data-name="<?= $source['name'] ?>" data-description="<?= $source['description'] ?>" data-content="Add records to this source" data-toggle="tooltip" data-placement="top" title="Import Records">
							<i class="fa fa-plus"></i>
						</button>
					<?php endif; ?>
				<?php else: ?>
						<a href="#" rel="popover" data-content="You cannot edit or import records for a federated source. This must be done via the source installation." data-original-title="Import Records" ><i class="fa fa-minus"></i></a>
				<?php endif; ?>
			</td>
			<td>
				<?php
				if ( $source['type'] != "api" && $source['type'] != "central" ):
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
				else:?>
					<a href="#" rel="popover" data-content="You cannot assign groups federated source. All access to these records is controlled via the source installation." data-original-title="Cannot Edit Groups" >
						<i class="fa fa-minus"></i>
					</a>
				<?php endif; ?>
			</td>
			<td>
			<?php if ( $source['type'] != "api" && $source['type'] != "central" ): ?>
				<a href="<?php echo base_url('source/edit_source'). "/" . $source['source_id']; ?>" rel="popover" data-content="Modify curators, groups general information for this source" data-original-title="Edit Source">
					<i class="fa fa-edit"></i>
				</a>
			<?php endif; ?>
				<a href="<?php echo base_url('source/delete_source'). "/" . $source['source_id'] . "/" . $source['name']; ?>" rel="popover" data-content="Delete the source entry. N.B. records related to this source will not be deleted from the database." data-original-title="Delete Source">
					<i class="fa fa-trash"></i></a>
				<a href="<?php echo base_url('source/status'). "/" . $source['name']; ?>" rel="popover" data-content="View the the status of uploaded files to this source." data-original-title="Source File Status">
					<i class="fa fa-info-circle"></i>
				</a>
			</td>
			<td>
			<?php if ( $source['status'] == "online" ): ?>
			<div class="custom-control custom-switch">
				<input type="checkbox" class="custom-control-input" id="<?php echo $source['name']; ?>" checked>
				<label class="custom-control-label" for="<?php echo $source['name']; ?>">Status</label>				
			</div>
			<?php elseif ( $source['status'] == "offline" ): ?>
			<div class="custom-control custom-switch">
				<input type="checkbox" class="custom-control-input" id="<?php echo $source['name']; ?>" unchecked>
				<label class="custom-control-label" for="<?php echo $source['name']; ?>">Status</label>				
			</div>
			<?php endif; ?>
			</td>
		</tr>		
		<?php endforeach; ?>
	</tbody>
</table>

<div id="sourceDisplay"></div>

<br />

<div class="row">
	<div class="col">
		<a href="#" class="btn btn-primary btn-medium" data-target="#addSourceModal" data-toggle="modal" data-backdrop="false" rel="popover" data-content="Fill in a form to add a new source to your installation." data-original-title="Add Source">
			<i class="fa fa-file"></i>  Add source
		</a>
		<a class="btn btn-primary btn-medium" href="<?php echo base_url('admin/variants') ?>" data-content="Switches to the records admin interface to allow you to modify records." data-original-title="Edit Records">
			<i class="fa fa-edit"></i>  Edit records
		</a>
		<a href="<?php echo base_url() . "admin";?>" class="btn btn-secondary" >
			<i class="fa fa-home"></i> Admin Dashboard
		</a>
	</div>
</div>



<div id="addSourceModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addSourceModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" align="center" id="addSourceModalTitle">Add a new source</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
          			<span aria-hidden="true">&times;</span>
        		</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col">
						<a class="btn btn-primary btn-medium" href="<?php echo base_url('source/add_source') ?>" >
							<i class="fa fa-file"></i>  Add local source
						</a>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<small class="form-text text-muted">Create a new source in your local installation to which records can be added.</small>				
					</div>
				</div>
				<?php if ($setting->settingData['federated']): ?>
				<hr/>
				<div class="row">
					<div class="col">
					<a class="btn btn-primary btn-medium" href="<?php echo base_url('sources/add_federated_source') ?>" >
						<i class="fa fa-file"></i>  Add federated source</a>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<small class="form-text text-muted">Select which federated sources are discoverable, N.B. you must have set up federated source details in the settings page of the admin dashboard.</small>				
					</div>
				</div>	
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>			
		</div>
	</div>



</div>

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
				<div class="row">
					<div class="col">
						<a id="bulkImport" class="btn btn-small btn-primary">
								<i class="fa fa-plus"></i> Bulk import records
						</a>
					</div>				
				</div>
				<div class="row">
					<div class="col">
						<p>
							<i>Use a bulk import tool to upload multiple records at once (various formats accepted).</i>
						</p>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<a id="phenoPacketsImport" class="btn btn-small btn-primary">
							<i class="fa fa-plus"></i> Import PhenoPackets
						</a>
					</div>		
				</div>	
				<div class="row">
					<div class="col">
						<p>
							<i>Use a bulk import tool to upload multiple PhenoPackets at once.</i>
						</p>
					</div>
				</div>					
			</div>
			<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>		
		</div>
	</div>

</div>

<!-- This modal is apparently not used. -->
<div id="shareModal<?php echo $c; ?>" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 align="center" id="myModalLabel">Share <?php echo $source['description']; ?></h4>
	</div>
	<div class="modal-body">
		<div class="well">
			<?php if (false)://(array_key_exists($source['source_id'], $source_groups)): ?>
				<p align="center">Enter the email address and select which group the invited user will be added to:</p><hr>
				Email:<br />
				<input id="email<?php echo $c; ?>" name="email<?php echo $c; ?>" type="text" /><br /><br />
				Add to group:<br />
				
				<select name="groups<?php echo $c; ?>" id="groups<?php echo $c; ?>" >
				<?php foreach ($source_groups[$source['source_id']] as $group): ?>
					<option value="<?php echo $group['group_id']; ?>"><?php echo $group['group_name']; ?></option>
				<?php endforeach; ?>
				</select>
				<br /><br /><p>N.B. If you want to add an existing user to a source group, go to the <a href="<?php echo base_url('auth/users'); ?>">edit users</a> admin page. To assign groups to this source go to the <a href="<?php echo base_url('sources/edit_source'). "/" . $source['source_id']; ?>">edit source</a> admin page.</p>
			<?php else: ?>
				No groups have been assigned to this source, click the button below to edit the source and assign groups:<br /><br />
				<a href="<?php echo base_url('admin/edit_source'). "/" . $source['source_id']; ?>" rel="popover" data-content="Modify curators, groups general information for this source" data-original-title="Edit Source"><i class="icon-edit"></i></a>
			<?php endif; ?>
			<div id="shareDiv<?php echo $c; ?>"></div>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#" onclick="shareVariantsByEmail('<?php echo $c; ?>');" class="btn btn-success">Share</a>
		<a href="#" class="btn" data-dismiss="modal">Close</a>  
	</div>
</div>
<!-- This modal is apparently not used. -->
<div class="modal fade" id="cloneSourceModal" tabindex="-1" role="dialog" aria-labelledby="cloneSourceModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h4 align="center" id="myModalLabel">Clone records from a source into a new source</h4>
	</div>
	<div class="modal-body">
		<div class="well">
			<table border="0">
				<tr>
					<td><strong>Clone source:</strong></td>
					<td>
						<select name="clone_source" id="clone_source" >
						<?php foreach ($sources as $source): ?>
							<option value="<?php echo $source['name']; ?>" ><?php echo $source['description']; ?></option>
						<?php endforeach; ?>
						</select>
					</td>
				<tr><td><hr></td></tr>
				<tr>
					<td><strong>Destination source name:</strong></td>
					<td><input id="clone_name" name="clone_name" type="text" /></td>
				</tr>
				<tr>
					<td><strong>Destination source description:</strong>&nbsp;&nbsp;</td>
					<td><input id="clone_description" name="clone_description" type="text" /></td>
				</tr>
			</table>
			<hr>
			<p>N.B. All other metadata from the original source will be copied and left unchanged, e.g. source owner, email etc. However, assigned groups and curators will NOT be copied to the new source and must be edited in the source details as normal.</p>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#" onclick="cloneSource();" class="btn btn-success">Clone</a>
		<a href="#" class="btn" data-dismiss="modal">Close</a>  
	</div>
</div>

<?= $this->endSection() ?>