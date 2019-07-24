<div class="container">
	<div class="row">  
		<div class="span6">  
			<ul class="breadcrumb">  
				<li>  
					<a href="<?php echo base_url() . "admin";?>">Dashboard Home</a> <span class="divider">></span>  
				</li>
				<li>
					<a href="<?php echo base_url() . "sources";?>">Sources</a> <span class="divider">></span>
				</li>
				<li class="active">Edit Source</li>
			</ul>  
		</div>  
	</div>
	<div class="row-fluid">
		<div class="span10 offset1 pagination-centered">
			<div class="well">
			<h2>Edit Source</h2>
			<p>Please enter the source information below.</p>
			<div id="infoMessage"><strong><?php echo $message; ?></strong></div>
			<?php
				$hidden = array('source_id' => $source_id);
				echo form_open("sources/edit_source", '', $hidden);
			?>
			<p>
				Source Name: <br />
				<?php echo form_input($name); ?>
			</p>
			<p>
				Source Description: <br />
				<?php echo form_input($desc); ?>
			</p>
			<p>
				Long Source Description: <br />
				<?php echo form_textarea($long_description); ?>
			</p>
			<p>
				Owner Email: <br />
				<?php echo form_input($email); ?>
			</p>
			<p>
				Source URI: <br />
				<?php echo form_input($uri); ?>
			</p>
			<p>
				<!--Source Type: <br />-->
				<?php
//				$options = array(
//					'varioml' => 'VarioML',
//					'mysql' => 'MySQL',
//					'vcf' => 'Variant Call Format',
//				);
//				echo form_dropdown('type', $options, $source_data['type'], 'disabled="disabled"');
				?>
			</p>

			<p>
				Status: <br />
				<?php
				$options = array(
					'online' => 'Online',
					'offline' => 'Offline',
				);
				echo form_dropdown('status', $options, $source_data['status']);
				?>
			</p>

			<p>
				<?php if (array_key_exists('error', $groups)): ?>
				<p><span class="label label-important">There are no network groups available to this installation. <br /></span></p>
				<?php else: ?>
				Assign network groups to source:<br />
                                
                                <h3>Source Display Groups</h3>
                                <div class="row-fluid">
                                    <div class="span5 pagination-centered">
                                        <select size="5" multiple id="sdg_left">
                                        </select>
                                    </div>
                                    <div class="span2 pagination-centered">
                                        <br><input type="button" value="&gt;&gt;"/><br><br>
                                        <input type="button" value="&lt;&lt;"/>
                                    </div>
                                    <div class="span5 pagination-centered">
                                        <select size="5" multiple id="sdg_right" name="groups[]" class="groupsSelected">
                                        </select>
                                    </div>
                                </div>
                                
                                <h3>Count Display Groups</h3>
                                <div class="row-fluid">
                                    <div class="span5 pagination-centered">
                                        <select size="5" multiple id="cdg_left">
                                        </select>
                                    </div>
                                    <div class="span2 pagination-centered">
                                        <br><input type="button" value="&gt;&gt;"/><br><br>
                                        <input type="button" value="&lt;&lt;"/>
                                    </div>
                                    <div class="span5 pagination-centered">
                                        <select size="5" multiple id="cdg_right" name="groups[]" class="groupsSelected">
                                        </select>
                                    </div>
                                </div>
                                
                                <?php foreach ($groups as $group ):
                                    if ($group['group_type'] === "source_display"):
                                        if(isset($selected_groups) && array_key_exists($group['id'], $selected_groups)): ?>
                                            <script type="text/javascript">
                                                $("#sdg_right").append($("<option></option>")
                                                .attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
                                                .text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
                                            </script>
                                        <?php else: ?>
                                            <script type="text/javascript">
                                                $("#sdg_left").append($("<option></option>")
                                                .attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
                                                .text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
                                            </script>
                                        <?php endif;
                                    elseif ($group['group_type'] === "count_display"):
                                        if(isset($selected_groups) && array_key_exists($group['id'], $selected_groups)): ?>
                                            <script type="text/javascript">
                                                $("#cdg_right").append($("<option></option>")
                                                .attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
                                                .text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
                                            </script>
                                        <?php else: ?>
                                            <script type="text/javascript">
                                                $("#cdg_left").append($("<option></option>")
                                                .attr("value",'<?php echo $group['id'] . "," . $group['network_key'] . ""; ?>')
                                                .text('<?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?>')); 
                                            </script>
                                        <?php endif;
                                    endif;
                                endforeach; ?>
				<?php endif; ?>
			</p>
			
			
                        <p><button type="submit" onclick="select_groups()" name="submit" class="btn btn-primary"><i class="icon-file"></i>  Save Source</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo base_url() . "sources/sources";?>" class="btn" ><i class="icon-step-backward"></i> Go back</a></p>
		
			</div>
		</div>
	</div>
</div>
<?php echo form_close(); ?>