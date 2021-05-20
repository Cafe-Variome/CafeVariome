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

<?php echo form_open($controllerName.'/Update/' . $pipeline_id); ?>
<?php echo form_hidden('id', $pipeline_id) ?>
<div class="form-group row">
    <div class="col-6">
        <?php echo form_label('Name', 'name'); ?>
        <?php echo form_input($name); ?>
    </div>
    <div class="col-6">
    </div>
</div>

<div class="form-group row">
    <div class="col-6">
        <?php echo form_label('Subject ID Location', 'subject_id_location'); ?>
        <?php echo form_dropdown($subject_id_location); ?>
    </div>
    <div class="col-6">
        <?php echo form_label('Subject ID Attribute Name', 'subject_id_attribute_name'); ?>
        <?php echo form_input($subject_id_attribute_name); ?>
    </div>
</div>

<div class="form-group row">
    <div class="col-6">
        <?php echo form_label('Grouping', 'grouping'); ?>
        <?php echo form_dropdown($grouping);
	    ?>
    </div>
    <div class="col-6">
        <?php echo form_label('Group Columns', 'group_columns'); ?>
        <?php echo form_input($group_columns); ?>
    </div>
</div>

<div class="form-group row">
    <div class="col-6">
        <?php echo form_label('HPO Attribute Name', 'hpo_attribute_name'); ?>
        <?php echo form_input($hpo_attribute_name); ?>
    </div>
    <div class="col-6">
        <?php echo form_label('Negated HPO Attribute Name', 'negated_hpo_attribute_name'); ?>
        <?php echo form_input($negated_hpo_attribute_name); ?>
    </div>
</div>

<div class="form-group row">
    <div class="col-6">
        <?php echo form_label('ORPHA Attribute Name', 'orpha_attribute_name'); ?>
        <?php echo form_input($orpha_attribute_name); ?>
    </div>
    <div class="col-6">
        <?php echo form_label('Internal Delimiter', 'internal_delimiter'); ?>
        <?php echo form_input($internal_delimiter); ?>
    </div>
</div>

<div class="form-group row">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">Save Pipeline</button>
	<a href="<?php echo base_url($controllerName);?>" class="btn btn-secondary  bg-gradient-secondary" >Go back</a>	
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>