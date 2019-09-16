<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>
<?php if($message): ?>
<div class="alert alert-info">
    <?php echo $message; ?>
</div>
<?php endif ?>

<?php echo form_open("user/edit_user/". $user_id); ?>
    <?php echo form_hidden(array('installation_key' => $setting->settingData['installation_key'])); ?>	
    <div class="form-group">
        <?php echo form_label('Email as Username: (*)', 'email'); ?>
        <?php echo form_input($email, '', ['disabled' => 'disabled']); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('First Name: (*)', 'first_name'); ?>
        <?php echo form_input($first_name); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('Last Name: (*)', 'last_name'); ?>
        <?php echo form_input($last_name); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('Institute/Laboratory/Company Name: (*)', 'company'); ?>
        <?php echo form_input($company); ?>
    </div>
    <div class="form-group">
        <?php echo form_label('Add to Group (control click to select multiple):', 'groups'); ?>
        <button type="button" class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="right" title="Press and hold Ctrl key to select multiple items.">
            <i class="fa fa-question"></i>
        </button>   
        <?php if (array_key_exists('error', $groups)): ?>
        <div class="alert alert-info">
            There are no network groups available to this installation.
            A user will not be able to log in until they been assigned to at least one group.
        </div>  
    <?php else: ?>
        <?php $count = count($groups) + 1; $additional = 'size="' . $count . '"'; ?>
        <select size="<?php echo $count; ?>" name="groups[]"  multiple="multiple" class="form-control">
            <?php foreach ($groups as $group): ?>
            <?php if (array_search($group['id'], $selected_groups) !== false): ?>
                <option value="<?php echo $group['id'] . "," . $group['network_key']; ?>" selected><?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?></option>
            <?php else: ?>
                <option value="<?php echo $group['id'] . "," . $group['network_key']; ?>"><?php echo $group['description'] . " (Network:" . $group['network_name'] . ")"; ?></option>
            <?php endif ?>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?php echo form_checkbox($isadmin); ?>
            <?php echo form_label('Assign admin rights to user for this installation.', 'isadmin', array("class"=>"custom-control-label")); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?php echo form_checkbox($isremote); ?>
            <?php echo form_label('This user is a remote user from a different installation.', 'isremote', array("class"=>"custom-control-label")); ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col">
            <button type="submit" name="submit" class="btn btn-primary">
                <i class="fa fa-save"></i>  Save User
            </button>
            <a href="<?php echo base_url() . "user/users"; ?>" class="btn btn-secondary" >
                <i class="fa fa-backward"></i> Go back
            </a>
        </div>
    </div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>