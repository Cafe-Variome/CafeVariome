<?php
/**
    *@author Mehdi Mehtarizadeh
    *Created 05/02/2020
    *This is the dashboard layout for administrator panel.
    *It is based on SB Admin 2.
*/
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="keywords" content="<?php echo $meta_keywords ?>" />
        <meta name="author" content="<?php echo $meta_author ?>" />
        <meta name="description" content="<?php echo $meta_description ?>" />

        <title><?= $site_title ?> - <?= $title ?></title>

        <!-- Custom fonts for this template-->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo base_url(CSS . "dashboard/sbadmin/sb-admin-2.css");?>" type="text/css"/>
        <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/fontawesome.css"); ?>" type="text/css"/>
        <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/brands.css"); ?>" type="text/css"/>
        <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/solid.css"); ?>" type="text/css"/>
        <link rel="stylesheet" href="<?php echo base_url(VENDOR . "select2/select2/dist/css/select2.css");?>" type="text/css"/>
        <link rel="stylesheet" href="<?php echo base_url(CSS . "dashboard/dashboard.css");?>" type="text/css"/>
        <!-- extra CSS-->
        <?php foreach($css as $c):?>
        <link rel="stylesheet" href="<?php echo base_url($c) ?>">
        <?php endforeach;?>

        <!-- favicon -->
        <link rel="shortcut icon" href="<?php echo base_url(IMAGES.'logos/favicon.ico');?>" />
		<script type="text/javascript" src="<?= base_url('UserInterfaceAPI/GetUIConstants') ?>"></script>

        <script src="<?php echo base_url(JS."jquery-3.6.0.min.js");?>"></script>
    </head>

    <body>
		<div id="layoutSidenav">
			<div id="layoutSidenav_nav">
				<nav class="sb-sidenav accordion bg-gradient-secondary" id="sidenavAccordion">
					<div class="sb-sidenav-menu">
						<div class="nav">
							<a class="navbar-brand d-flex align-items-center" href="<?= base_url('Home/Index') ?>">
								<div class="rotate-n-15">
									<img src="<?= base_url(IMAGES . "cafevariome/cafevariome_icon.png") ?>" />
								</div>
								<div class="navbar-heading">
									<?= $site_title ?>
								</div>
							</a>

							<hr class="sidebar-divider">

							<a class="nav-link <?= $uriSegments->methodName == 'index' ? 'active' : ''?>" href="<?= base_url('Admin/Index') ?>">
								<div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
								Dashboard
							</a>

							<hr class="sidebar-divider">

							<div class="sidebar-heading">Discovery</div>
							<a class="nav-link active" href="<?= base_url('Discover/Index') ?>">
								<div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
								Discover
							</a>

							<hr class="sidebar-divider">

							<div class="sidebar-heading">Data</div>

							<a class="<?= $controllerName == 'Pipeline' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePipelines" aria-expanded="<?= $controllerName == 'Pipeline' ? 'true' : 'false'?>" aria-controls="collapsePipelines">
								<div class="sb-nav-link-icon"> <i class="fas fa-grip-lines-vertical"></i></div>
								Pipelines
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Pipeline' ? 'show' : ''?>" id="collapsePipelines" aria-labelledby="headingPipelines" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Pipeline/Create') ?>">Create a Pipeline</a>
										<a class="nav-link-item" href="<?= base_url('Pipeline/List') ?>">View Pipelines</a>
									</nav>
								</div>

							</div>

							<a class="<?= $controllerName == 'Source' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSources" aria-expanded="false" aria-controls="collapseSources">
								<div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
								Sources
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Source' ? 'show' : ''?>" id="collapseSources" aria-labelledby="headingSources" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Source/Create') ?>">Create a Source</a>
										<a class="nav-link-item" href="<?= base_url('Source/List') ?>">View Sources</a>
									</nav>
								</div>
							</div>

							<a class="<?= $controllerName == 'Ontology' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseOntologies" aria-expanded="false" aria-controls="collapseOntologies">
								<div class="sb-nav-link-icon"><i class="fas fa-project-diagram"></i></div>
								Ontologies
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Ontology' ? 'show' : ''?>" id="collapseOntologies" aria-labelledby="headingOntologies" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Ontology/Create') ?>">Create an Ontology</a>
										<a class="nav-link-item" href="<?= base_url('Ontology/List') ?>">View Ontologies</a>
									</nav>
								</div>
							</div>

							<hr class="sidebar-divider">

							<div class="sidebar-heading">Network</div>

							<a class="<?= $controllerName == 'Network' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseNetworks" aria-expanded="false" aria-controls="collapseNetworks">
								<div class="sb-nav-link-icon"><i class="fas fa-network-wired"></i></div>
								Networks
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Network' ? 'show' : ''?>" id="collapseNetworks" aria-labelledby="headingNetworks" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Network/Create') ?>">Create a Network</a>
										<a class="nav-link-item" href="<?= base_url('Network/Join') ?>">Join a Network</a>
										<a class="nav-link-item" href="<?= base_url('Network/List') ?>">View Networks</a>
										<a class="nav-link-item" href="<?= base_url('NetworkRequest/List') ?>">View Network Requests</a>
									</nav>
								</div>
							</div>

							<a class="<?= $controllerName == 'DiscoveryGroup' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseDiscoveryGroups" aria-expanded="false" aria-controls="collapseDiscoveryGroups">
								<div class="sb-nav-link-icon"><i class="fas fa-user-friends"></i></div>
								Discovery Groups
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'DiscoveryGroup' ? 'show' : ''?>" id="collapseDiscoveryGroups" aria-labelledby="headingDiscoveryGroups" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('DiscoveryGroup/Create') ?>">Create a Discovery Group</a>
										<a class="nav-link-item" href="<?= base_url('DiscoveryGroup/List') ?>">View Discovery Groups</a>
									</nav>
								</div>
							</div>

							<hr class="sidebar-divider">

							<div class="sidebar-heading">Access Control</div>

							<a class="<?= $controllerName == 'User' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUsers" aria-expanded="false" aria-controls="collapseUsers">
								<div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
								Users
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'User' ? 'show' : ''?>" id="collapseUsers" aria-labelledby="headingUsers" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('User/Create') ?>">Create a User</a>
										<a class="nav-link-item" href="<?= base_url('User/List') ?>">View Users</a>
									</nav>
								</div>
							</div>

							<hr class="sidebar-divider">

							<div class="sidebar-heading">Content</div>

							<a class="<?= $controllerName == 'Page' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages">
								<div class="sb-nav-link-icon"><i class="fas fa-file"></i></div>
								Pages
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Page' ? 'show' : ''?>" id="collapsePages" aria-labelledby="headingPages" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Page/Create') ?>">Create a Page</a>
										<a class="nav-link-item" href="<?= base_url('Page/List') ?>">View Pages</a>
									</nav>
								</div>
							</div>

							<hr class="sidebar-divider">

							<div class="sidebar-heading">System</div>

							<a class="<?= $controllerName == 'Server' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseServers" aria-expanded="false" aria-controls="collapseServers">
								<div class="sb-nav-link-icon"><i class="fas fa-server"></i></div>
								Servers
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Server' ? 'show' : ''?>" id="collapseServers" aria-labelledby="headingServers" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Server/Create') ?>">Create a Server</a>
										<a class="nav-link-item" href="<?= base_url('Server/List') ?>">View Servers</a>
									</nav>
								</div>
							</div>

							<a class="<?= $controllerName == 'ProxyServer' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseProxyServers" aria-expanded="false" aria-controls="collapseProxyServers">
								<div class="sb-nav-link-icon"><i class="fas fa-ethernet"></i></div>
								Proxy Servers
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'ProxyServer' ? 'show' : ''?>" id="collapseProxyServers" aria-labelledby="headingProxyServers" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('ProxyServer/Create') ?>">Create a Proxy Server</a>
										<a class="nav-link-item" href="<?= base_url('ProxyServer/List') ?>">View Proxy Servers</a>
									</nav>
								</div>
							</div>

							<a class="<?= $controllerName == 'SingleSignOnProvider' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSingleSignOnProviders" aria-expanded="false" aria-controls="collapseSingleSignOnProviders">
								<div class="sb-nav-link-icon"><i class="fas fa-sign-in-alt"></i></div>
								Single Sign on Providers
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'SingleSignOnProvider' ? 'show' : ''?>" id="collapseSingleSignOnProviders" aria-labelledby="headingSingleSignOnProviders" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('SingleSignOnProvider/Create') ?>">Create a Provider</a>
										<a class="nav-link-item" href="<?= base_url('SingleSignOnProvider/List') ?>">View Providers</a>
									</nav>
								</div>
							</div>

							<a class="<?= $controllerName == 'Credential' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCredentials" aria-expanded="false" aria-controls="collapseCredentials">
								<div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
								Credentials
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Credential' ? 'show' : ''?>" id="collapseCredentials" aria-labelledby="headingCredentials" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Credential/Create') ?>">Create a Credential</a>
										<a class="nav-link-item" href="<?= base_url('Credential/List') ?>">View Credentials</a>
									</nav>
								</div>
							</div>

							<a class="<?= $controllerName == 'Setting' ? 'nav-link active' : 'nav-link collapsed'?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSettings" aria-expanded="false" aria-controls="collapseSettings">
								<div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
								Settings
								<div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
							</a>
							<div class="collapse <?= $controllerName == 'Setting' ? 'show' : ''?>" id="collapseSettings" aria-labelledby="headingSettings" data-bs-parent="#sidenavAccordion">
								<div class="collapse-inner">
									<nav class="sb-sidenav-menu-nested nav">
										<a class="nav-link-item" href="<?= base_url('Setting/Main') ?>">System Settings</a>
										<a class="nav-link-item" href="<?= base_url('Setting/Elasticsearch') ?>">Elastic Search Settings</a>
										<a class="nav-link-item" href="<?= base_url('Setting/Neo4J') ?>">Neo4J Settings</a>
										<a class="nav-link-item" href="<?= base_url('Setting/Discovery') ?>">Discovery Settings</a>
										<a class="nav-link-item" href="<?= base_url('Setting/Endpoint') ?>">Endpoint Settings</a>
									</nav>
								</div>
							</div>
						</div>
					</div>
					<div class="sb-sidenav-footer">
						<div class="small">Logged in as:</div>
						<?= $userName ?>
					</div>
				</nav>
			</div>
			<div id="layoutSidenav_content">
				<nav class="sb-topnav navbar navbar-expand shadow">
					<button class="btn btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
					<ul class="navbar-nav ms-auto me-md-0 me-3 me-lg-0">
						<li class="nav-item dropdown ms-auto me-2">
							<a class="top-nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="fas fa-user fa-fw"></i>
							</a>
							<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
								<li>
									<a class="dropdown-item" href="<?= $profileURL ?>">
										<i class="fas fa-user ms-3"></i>  Profile
									</a>
								</li>
								<li><hr class="dropdown-divider"></li>
								<li>
									<a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
										<i class="fas fa-sign-out-alt  ms-3"></i>  Logout
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</nav>
				<main>
					<div class="container-fluid px-4">
					<?= $this->renderSection('content') ?>
					</div>
				</main>
				<footer class="py-4 bg-light mt-auto">
					<div class="container-fluid px-4">
						<div class="text-center justify-content-between small">
							<div class="row">
								<div class="col">
									Powered by <a target="_blank" href="https://www.cafevariome.org/">Café Variome </a> (v. <?= $version ?>)
									| <a href="" data-toggle="modal" data-target="#privacyPolicyModal">Privacy Policy</a>
								</div>
							</div>
							<div class="row">
								<div class="col">Copyright &copy; <?= date("Y") . ', University of Leicester' ?></div>
							</div>
						</div>
					</div>
				</footer>
			</div>
		</div>
		<script src="<?= base_url(JS."dashboard/sbadmin/sb-admin-2.js")?>"></script>
		<!-- Bootstrap core JavaScript-->
		<script src="<?php echo base_url(VENDOR."twbs/bootstrap/dist/js/bootstrap.bundle.js");?>"></script>
		<script src="<?php echo base_url(VENDOR."select2/select2/dist/js/select2.js");?>"></script>

		<!-- extra Java Script-->
		<?php foreach($javascript as $js):?>
			<script src="<?php echo base_url($js)?>"></script>
		<?php endforeach;?>

		<script type="text/javascript">
			const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
			const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
		</script>
		<!-- Logout Modal-->
		<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
					<div class="modal-footer">
						<button class="btn btn-secondary bg-gradient-secondary" type="button" data-dismiss="modal">Cancel</button>
						<a class="btn btn-warning bg-gradient-warning" href="<?= base_url('Auth/Logout') ?>">
							<i class="fas fa-sign-out-alt fa-sm fa-fw"></i> Logout
						</a>
					</div>
				</div>
			</div>
		</div>
		<div id="privacyPolicyModal" class="modal fade" style="justify-content: center;align-items:center;" tabindex="-1" role="dialog" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content" style="width: 1200px;">
					<div class="modal-header">
						<h4 class="modal-title" id="privacyPolicyModalTitle">Our privacy policy</h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body" >
						<div class="row">
							<div class="col">
								<embed src="<?= base_url("PrivacyPolicy.pdf");?>" frameborder="0" width="800px" height="600px">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>










