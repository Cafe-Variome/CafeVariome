<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
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

<?php echo form_open($controllerName.'/Create'); ?>

<div class="row mb-3">
    <div class="col-6">
        <?php echo form_label('Name', 'name', ['class' => 'form-label']); ?>
        <?php echo form_input($name); ?>
    </div>
    <div class="col-6">
    </div>
</div>

<div class="row mb-3">
    <div class="col-6">
        <?php echo form_label('Subject ID Location', 'subject_id_location', ['class' => 'form-label']); ?>
        <?php echo form_dropdown($subject_id_location); ?>
    </div>
    <div class="col-6">
        <?php echo form_label('Subject ID Attribute Name', 'subject_id_attribute_name', ['class' => 'form-label']); ?>
        <?php echo form_input($subject_id_attribute_name); ?>
    </div>
</div>

<div class="row mb-3" id="subject_id_prefix_batch">
	<div class="col-6">
		<?php echo form_label('Subject ID Prefix', 'subject_id_prefix', ['class' => 'form-label']); ?>
		<?php echo form_input($subject_id_prefix); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Batch size of records to assign Subject ID to', 'subject_id_batch_size', ['class' => 'form-label']); ?>
		<?php echo form_input($subject_id_batch_size); ?>
	</div>
</div>

<div class="row mb-3" id="subject_id_expansion_on_columns">
	<div class="col-4">
		<?php echo form_label('Column(s) to Expand on', 'subject_id_expansion_columns', ['class' => 'form-label']); ?>
		<?php echo form_input($subject_id_expansion_columns); ?>
	</div>
	<div class="col-4">
		<?php echo form_label('Policy of Expansion', 'subject_id_expansion_policy', ['class' => 'form-label']); ?>
		<?php echo form_dropdown($subject_id_expansion_policy); ?>
	</div>
	<div class="col-4">
		<?php echo form_label('New Attribute Name', 'expansion_attribute_name', ['class' => 'form-label']); ?>
		<?php echo form_input($expansion_attribute_name); ?>
	</div>
</div>

<div class="row mb-3">
    <div class="col-6">
        <?php echo form_label('Grouping', 'grouping', ['class' => 'form-label']); ?>
        <?php echo form_dropdown($grouping); ?>
    </div>
    <div class="col-6">
        <?php echo form_label('Group Columns', 'group_columns', ['class' => 'form-label']); ?>
        <?php echo form_input($group_columns); ?>
    </div>
</div>

<div class="row mb-3">
    <div class="col-6">
		<?php echo form_label('Internal Delimiter', 'internal_delimiter', ['class' => 'form-label']); ?>
		<?php echo form_input($internal_delimiter); ?>
    </div>
    <div class="col-6">
    </div>
</div>

<div class="row mb-3">
	<div class="col">
	<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
		<i class="fa fa-plus"></i> Create Pipeline
	</button>
	<a href="<?= base_url($controllerName);?>" class="btn btn-secondary bg-gradient-secondary" >
		<i class="fa fa-grip-lines-vertical"></i> View Pipelines
	</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
