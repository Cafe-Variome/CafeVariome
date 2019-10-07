<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
	<a class="navbar-brand" href="<?php echo base_url() . (( ! $setting->settingData['cafevariome_central'] ) ? "home" : ''); ?>">
		<!--<img width="123" height="60" src="<?php echo base_url() . "resources/images/logos/" . $setting->settingData['logo'];?>">-->
		<?= $setting->settingData['site_title'] ?>
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
				<a href="<?= base_url("auth/login") ?>" class="nav-link" id="loginUser">Login</a>
			</li>
			<li class="nav-item">
				<a href="<?= base_url("auth/signup") ?>" class="nav-link">Sign up</a>
			</li>
		<?php else: ?>
			<li class="nav-item">
				<span class="nav-link text-white">Hello <?= $auth->getName() ?>!</span>
			</li>
			<li class="nav-item">
				<a class="nav-link<?= ($uriSegments->controllerName == 'discover') ? " active": "" ?>" href="<?= base_url("discover/index") ?>">
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
