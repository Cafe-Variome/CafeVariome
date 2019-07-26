<?php

$routes = \Config\Services::routes(true);

$current_cms_view = $session->get('current_cms_view');

$current_page = ucfirst(uri_string());
$current_controller = uri_string();

//if ( strtolower($current_page) == "discover" || strtolower($current_page) == "auth" || strtolower($current_page) == "admin" || strtolower($current_page) == "messages" ) {
//	$current_cms_view = "";
//}
?>
<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
	<a class="navbar-brand" href="<?php echo base_url() . (( ! $setting->settingData['cafevariome_central'] ) ? "home" : ''); ?>">
		<!--<img width="123" height="60" src="<?php echo base_url() . "resources/images/logos/" . $setting->settingData['logo'];?>">-->
		Cafe Variome
	</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
		<div class="mt-2 mt-md-0 mr-auto">
		</div>

		<ul class="navbar-nav">
		<?php if (! $auth->loggedIn()): ?>	
			<?php if ( ! preg_match('/login/i', $this->uri->rsegment(2))): ?>
				<li class="nav-item">
					<a href="<?php echo base_url() . "auth_federated/login";?>" class="nav-link" id="loginUser">Login</a>
				</li>
				<?php if ( false ):  // if ( $this->config->item('allow_registrations') ): ?>
					<?php if ( ! preg_match('/signup/i', $this->uri->rsegment(2))): ?>
					<li class="nav-item">
						<a href="<?php echo base_url() . "auth_federated/signup";?>" class="nav-link">Sign up</a>
					</li>
					<?php endif; ?>
				<?php endif; ?>
			<?php else: ?>
				<?php if(false): // if ( $this->config->item('allow_registrations') ): ?>
					<?php if ( ! preg_match('/signup/i', $this->uri->rsegment(2))): ?>
					<li class="nav-item">
						<a href="<?php echo base_url() . "auth_federated/signup";?>" class="nav-link">Sign up</a>
					</li>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>		
		<?php else: ?>
		<?php $user_id = $session->get( 'user_id' ); ?>
			<?php if ( (($session->get( 'controller' ) === "auth_federated") && $session->get( 'is_admin' )) /*|| ($this->ion_auth->is_admin())*/): ?>
			<li class="nav-item">
				<span class="nav-link text-white">Hello <?= $session->get('first_name') ?>!</span>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo base_url(); ?>adminv/chooseAdmin">
					Admin
				</a> 
			</li>
			<?php endif; ?>
			<?php if (false)://($this->ion_auth->in_group("curator")): ?>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo base_url(); ?>curate">
					Curate
				</a>
			</li>
			<?php endif; ?>
			<?php if ($setting->settingData['messaging']): ?>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo base_url(); ?>messages">
					Messages
				</a> 
			</li>
			<?php endif; ?>
			<li class="nav-item">
				<a class="nav-link" href="<?php echo base_url() . "auth_federated/user_profile/" . $user_id;?>">
					Profile
				</a> 
			</li>
			<li class="nav-item">
				<a class="nav-link" href="<?php if($session->get('controller') === "auth_federated") echo base_url() . "auth_federated/logout"; else echo base_url() . "auth/logout"; ?>">Logout</a>	
			</li>	
		<?php endif; ?>
	
		</ul>	  
    </div>
</nav>



<div id="searchBarModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h3 id="myModalLabel">Data Discovery Search</h3>
	</div>
	<div class="modal-body">
		<h4>Enter a search term:</h4>
		<?php
			$search_data = array('name' => 'term', 'id' => 'navbar_term', 'class'=>"input-xlarge search-query term", 'placeholder' => "Start typing a search term..." ); 
			echo form_input($search_data);
		?>
		<p></p>
		<p><small><a href="<?php echo base_url("discover");?>" >Access full discovery interface (includes examples and phenotype tree)</a></small></p>
		<?php if (false)://($this->ion_auth->logged_in()): ?><p><small><a href="<?php echo base_url('/discover/search_history'); ?>">View search history</a></p></small><?php endif; ?>
	</div>
	<div class="modal-footer">
		<button type="submit" class="btn btn-primary" id="navbar-search"><i class="icon-search"></i> Discover Variants</button>
	</div>
</div>
		<!-- End ToDo -->


<?php if(file_exists("resources/elastic_search_status_incomplete")) { ?>
    <script>
        show_growl_elastic_search();
    </script>
<?php } ?>
