<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>

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
				Please choose an OpenID Connect Provider to login:
			</div>
		</div>
    <hr/>
	<?php echo form_open($controllerName . "/Login", ['method'=>'post']);?>
		<div class="row">
			<div class="col-6">
				<?php for($i = 0; $i < count($singleSignOnProviders); $i++): ?>
					<?php if($i % 2): ?>
						<div class="row">
					<?php endif; ?>
						<div class="col-3">
							<button type="submit" name="provider" style="border: 0; background: transparent" value="<?= $singleSignOnProviders[$i]->getID() ?>">
								<img src="<?= base_url('ContentAPI/SingleSignOnIcon/' . $singleSignOnProviders[$i]->getID()) ?>" alt="submit" />
							</button>
							<br>
							<?= $singleSignOnProviders[$i]->display_name ?>
						</div>
					<?php if($i % 2): ?>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
			<div class="col-6">
				<?php if ($localAuthentication): ?>
					<div class="form-group row">
						<div class="col-9">
							<label for="identity">Email:</label>
							<?php echo form_input($identity); ?>
						</div>
						<div class="col-3"></div>
					</div>
					<div class="form-group row">
						<div class="col-9">
							<label for="password">Password:</label>
							<?php echo form_input($password); ?>
						</div>
						<div class="col-3"></div>
					</div>
					<div class="form-group  row form-check">
						<div class="col-9">

						</div>
						<div class="col-3"></div>

					</div>
					<div class="form-group row">
						<div class="col-9">
							<?php echo form_checkbox('remember', '1', FALSE, 'id="remember"'); ?>
							<label class="form-check-label" for="remember">Remember?</label>

							<button type="submit" name="submit" class="btn btn-large btn-primary" onclick="login_user();">
								<i class="fas fa-user"></i>  Login
							</button>
						</div>
						<div class="col-3"></div>


					</div>
				<?php endif; ?>
			</div>
		</div>

	<?php echo form_close();?>
	</div>
</div>

<?= $this->endSection() ?>
