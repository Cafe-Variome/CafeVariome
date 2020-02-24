<nav class="navbar navbar-expand-md fixed-top">
	<!-- <img src="<?php echo base_url() . "resources/images/logos/" . $setting->settingData['logo'];?>"> -->
	<div class="cv-logo-square">
		<div class="mug-coffee">
			<div class="smoke-container">
			<svg width="60px" height="30px" viewbox="0 0 60 30">
				<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
				<g class="smokes" transform="translate(2.000000, 2.000000)" stroke="#BEBEBE" stroke-width="3">
					<g class="smoke-1">
					<path id="Shape1" d="M0.5,8.8817842e-16 C0.5,8.8817842e-16 3.5,5.875 3.5,11.75 C3.5,17.625 0.5,17.625 0.5,23.5 C0.5,29.375 3.5,29.375 3.5,35.25 C3.5,41.125 0.5,41.125 0.5,47"></path>
					</g>
					<g class="smoke-2">
					<path id="Shape2" d="M0.5,8.8817842e-16 C0.5,8.8817842e-16 3.5,5.875 3.5,11.75 C3.5,17.625 0.5,17.625 0.5,23.5 C0.5,29.375 3.5,29.375 3.5,35.25 C3.5,41.125 0.5,41.125 0.5,47"></path>
					</g>
					<g class="smoke-3">
					<path id="Shape3" d="M0.5,8.8817842e-16 C0.5,8.8817842e-16 3.5,5.875 3.5,11.75 C3.5,17.625 0.5,17.625 0.5,23.5 C0.5,29.375 3.5,29.375 3.5,35.25 C3.5,41.125 0.5,41.125 0.5,47"></path>
					</g>
				</g>
				</g>
			</svg>
			</div>
			<div class="mug"></div>
		</div>
	</div>	
	<a class="navbar-brand ml-3" href="<?php echo base_url() . (( ! $setting->settingData['cafevariome_central'] ) ? "home" : ''); ?>">
		<?= $heading ?>
	</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
		<div class="mt-2 mt-md-0 mr-auto">
		</div>

		<ul class="navbar-nav">
		<?php if (! $auth->loggedIn()): ?>	
			<li class="nav-item">
				<a href="<?= base_url("auth/login") ?>" class="nav-link-top" id="loginUser">Login</a>
			</li>
		<?php else: ?>
			<li class="nav-item">
				<span class="nav-link">Hello <?= $auth->getName() ?>!</span>
			</li>
			<li class="nav-item">
				<a class="nav-link-top"<?= ($uriSegments->controllerName == 'discover') ? " active": "" ?>" href="<?= base_url("discover/index") ?>">
					Discover
				</a> 
			</li>
			<?php if($auth->isAdmin()): ?>
			<li class="nav-item">
				<a class="nav-link<?= ($uriSegments->controllerName == 'admin') ? " active": "" ?>" href="<?= base_url("admin/index") ?>">
					Admin Dashboard
				</a> 
			</li>
			<?php else: ?>
			<li class="nav-item">
				<a class="nav-link<?= ($uriSegments->controllerName == 'auth') ? " active": "" ?>" href="<?= base_url("auth/index") ?>">
					Dashboard
				</a> 
			</li>
			<?php endif; ?>
			<?php if ($setting->settingData['messaging']): ?>
			<li class="nav-item">
				<a class="nav-link" href="<?= base_url("messages") ?>">
					Messages
				</a> 
			</li>
			<?php endif; ?>
			<li class="nav-item">
				<?php if ($auth->getAuthEngineName() === "app\libraries\keycloak"): ?>
				<a class="nav-link" href="<?= $setting->settingData['key_cloak_uri'] . "/realms/". $setting->settingData['key_cloak_realm'] ."/account/" ?>">
					Profile
				</a> 
				<?php else : ?>
				<a class="nav-link<?= ($uriSegments->methodName == 'edit_user') ? " active": "" ?>" href="<?= base_url("auth/edit_user/".$auth->getUserId()) ?>">
					Profile
				</a> 
				<?php endif ?>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?= base_url("auth/logout") ?>">Logout</a>	
			</li>	
		<?php endif; ?>
		</ul>	  
    </div>
</nav>
		<!-- End ToDo -->


<?php if(file_exists("resources/elastic_search_status_incomplete")) { ?>
    <script>
        //show_growl_elastic_search();
    </script>
<?php } ?>
