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

	<?php echo form_open($controllerName . "/Login", ['method'=>'post']);?>
		<div class="row">
			<div class="col-<?= $localAuthentication ? '6' : '12' ?>">
				<div class="card border-primary" style="height:600px">
					<div class="card-header bg-primary text-white">
						<h4><i class="fa fa-sign-in-alt mr-2"></i>Single Sign-on</h4>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col">
								Please choose a Single Sign-on Provider to login:
							</div>
						</div>
						<hr/>
				<div class="row pb-2">
				<?php for($i = 0; $i < count($singleSignOnProviders); $i++): ?>
						<div class="col-4 text-center">
							<button type="submit" name="provider" style="border: 0; background: transparent" value="<?= $singleSignOnProviders[$i]->getID() ?>">
								<img src="<?= base_url('ContentAPI/SingleSignOnIcon/' . $singleSignOnProviders[$i]->getID()) ?>" alt="submit" height="64" />
								<br>
								<?= $singleSignOnProviders[$i]->display_name ?>
							</button>
						</div>
					<?php  if($i > 0 && (($i+1) % 3) == 0): ?>
						</div><div class="row pb-2">
					<?php endif; ?>
				<?php endfor; ?>
						</div>
					</div>
				</div>
			</div>
			<?php if ($localAuthentication): ?>
				<div class="col-6">
				<div class="card border-primary" style="height:600px">
					<div class="card-header bg-primary text-white">
						<h4><i class="fa fa-key mr-2"></i>Local Login</h4>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col">
								If you have a local account, you can login using the form below:
							</div>
						</div>
						<hr/>
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
								</div>
								<div class="col-3"></div>
							</div>
							<div class="form-group row">
								<div class="col-9">
									<button type="submit" name="submit" class="btn btn-large btn-primary" onclick="login_user();">
										<i class="fas fa-user"></i>  Login
									</button>
								</div>
								<div class="col-3"></div>
							</div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

	<?php echo form_close();?>

<?= $this->endSection() ?>
