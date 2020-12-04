<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>

<?php if($message): ?>
<div class="row">
  <div class="col">
    <div class="alert alert-info">
      <?php echo $message ?>
    </div>
  </div>
</div>
<?php endif; ?>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="card border-primary">
  <div class="card-header bg-primary text-white">
    <h4><i class="fa fa-key mr-2"></i><?php echo lang('Auth.login_heading');?></h4>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col">
        <?php echo lang('Auth.login_subheading');?>
      </div>
    </div>
    <hr/>
    <?php echo form_open("auth/login", array('name' => 'loginUser'));?>
    <div class="form-group">
      <label for="identity">Email:</label>
      <?php echo form_input($identity); ?>
    </div>
    <div class="form-group">
      <label for="password">Password:</label>
      <?php echo form_input($password); ?>
    </div>
    <div class="form-group form-check">
      <?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>
      <label class="form-check-label" for="remember">Remember?</label>
    </div>    
    <div class="form-group">
      <button type="submit" name="submit" class="btn btn-large btn-primary" onclick="login_user();">
        <i class="fas fa-user"></i>  Login
      </button>
    </div>
    <div class="row">
      <div class="col-md-6">
        <!-- <p><a href="forgot_password">Forgot your password?</a></p> -->
      </div>
      <div class="col-md-6">
        <!-- <?php if ( $setting->settingData['allow_registrations'] ): ?><p><a href="signup">Register for a new account?</a></p>
        <?php endif; ?> -->
      </div>      
    </div>
<?php echo form_close();?>
  </div>
</div>

<?= $this->endSection() ?>
