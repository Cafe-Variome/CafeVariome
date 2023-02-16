<nav class="navbar navbar-expand-md navbar-light fixed-top">
	<div class="container-fluid">
		<?php if(strlen($headerImage) > 0): ?>
			<img src="<?= base_url($headerImage);?>">
		<?php else:?>
			<div class="cv-logo-square">
				<div class="mug-coffee">
					<div class="smoke-container">
						<svg viewbox="0 0 60 30">
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
		<?php endif; ?>
		<a class="navbar-brand text-dark ms-3" href="<?php echo base_url("home"); ?>">
			<?= $site_title ?>
		</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarCollapse">
			<ul class="navbar-nav ms-auto mb-2 mb-md-0 float-right">
			<?php if (! $loggedIn): ?>
				<li class="nav-item">
						<a class="nav-link-top" data-toggle="modal" data-target="#privacyPolicyModal">Privacy Policy</a>
				</li>
			<?php endif; ?>
			<?php if (! $loggedIn): ?>
				<li class="nav-item">
					<a href="<?= base_url("auth/login") ?>" class="nav-link-top<?= (strtolower($uriSegments->controllerName) == 'auth') ? " active": "" ?>" id="loginUser">Login</a>
				</li>
			<?php else: ?>
				<li class="nav-item">
					<span class="nav-link-top">Hello <?= $userName ?>!</span>
				</li>
				<li class="nav-item">
					<a class="nav-link-top<?= (strtolower($uriSegments->controllerName) == 'discover') ? " active": "" ?>" href="<?= base_url("discover/index") ?>">
						Discover
					</a>
				</li>
				<?php if($isAdmin): ?>
				<li class="nav-item">
					<a class="nav-link-top<?= (strtolower($uriSegments->controllerName) == 'admin') ? " active": "" ?>" href="<?= base_url("admin/index") ?>">
						Admin Dashboard
					</a>
				</li>
				<?php endif; ?>
				<li class="nav-item">
					<a target="_blank" class="nav-link-top" href="<?= $profileURL ?>">
						Profile
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link-top" href="<?= base_url("Auth/Logout") ?>">Logout</a>
				</li>
			<?php endif; ?>
			</ul>
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
	</div>
</nav>
