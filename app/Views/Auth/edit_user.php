<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
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

<?php echo form_open(uri_string());?>
<?php echo form_hidden('id', $user->id);?>

<div class="form-group">
    <?php echo form_label(lang('Auth.edit_user_fname_label'), 'first_name');?>
    <?php echo form_input($first_name);?>
</div>
<div class="form-group">
    <?php echo form_label(lang('Auth.edit_user_lname_label'), 'last_name');?> <br />
    <?php echo form_input($last_name);?>
</div>
<div class="form-group">
    <?php echo form_label(lang('Auth.edit_user_company_label'), 'company');?> <br />
    <?php echo form_input($company);?>
</div>
<div class="form-group">
    <?php echo form_label(lang('Auth.edit_user_phone_label'), 'phone');?> <br />
    <?php echo form_input($phone);?>
</div>
<div class="form-group">
    <?php echo form_label(lang('Auth.edit_user_password_label'), 'password');?> <br />
    <?php echo form_input($password);?>
</div>
<div class="form-group">
    <?php echo form_label(lang('Auth.edit_user_password_confirm_label'), 'password_confirm');?><br />
    <?php echo form_input($password_confirm);?>
</div>
<div class="form-group">
</div>

      <?php if ($ionAuth->isAdmin()): ?>

          <h5><?php echo lang('Auth.edit_user_groups_heading');?></h5>
          <?php foreach ($groups as $group):?>
              <label class="checkbox">
              <?php
                  $gID = $group['id'];
                  $checked = null;
                  $item = null;
                  foreach($currentGroups as $grp) {
                      if ($gID == $grp->id) {
                          $checked = ' checked="checked"';
                      break;
                      }
                  }
              ?>
              <input type="checkbox" name="groups[]" value="<?php echo $group['id'];?>"<?php echo $checked;?>>
              <?php echo htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');?>
              </label>
          <?php endforeach?>

      <?php endif ?>

<div class="form-group row">
    <div class="col">
        <?php echo form_submit('submit', lang('Auth.edit_user_submit_btn'), ['class' => 'btn btn-primary']);?>
    </div>
</div>

<?php echo form_close();?>

<?= $this->endSection() ?>