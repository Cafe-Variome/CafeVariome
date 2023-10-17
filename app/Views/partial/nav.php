<nav class="navbar navbar-expand-lg navbar-light bg-light">
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

		<a class="navbar-brand text-dark ms-3" href="<?php echo base_url("home"); ?>"><?= $site_title ?></a>

		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">

				<!-- User not logged in -->
				<?php if (! $loggedIn): ?>
					<li class="nav-item">
						<a class="nav-link-top btn btn" href="#" onclick="openPrivacyPolicyModal()">Privacy Policy</a>
					</li>

					<li class="nav-item">
						<a class="nav-link-top btn btn <?= (strtolower($uriSegments->controllerName) == 'auth') ? " active": "" ?>" href="<?= base_url("auth/login") ?>" >Login</a>
					</li>

				<?php else: ?>

					<!-- User logged in -->
					<li class="nav-item">
						<a class="nav-link-top btn btn<?= (strtolower($uriSegments->controllerName) == 'discover') ? " active": "" ?>" href="<?= base_url("discover/index") ?>">Discover</a>
					</li>

					<!-- Admin account -->
					<?php if($isAdmin): ?>
						<li class="nav-item">
							<a class="nav-link-top btn btn<?= (strtolower($uriSegments->controllerName) == 'admin') ? " active": "" ?>" href="<?= base_url("admin/index") ?>">Admin Dashboard</a>
						</li>
					<?php endif; ?>
					<!-- End -->
					<li class="nav-item dropdown">
						<a class="nav-link-top btn dropdown-toggle" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="fas fa-user"></i> Hello <?= $userName ?>
						</a>
						<ul class="dropdown-menu" aria-labelledby="navbarDarkDropdownMenuLink">
							<li><a class="dropdown-item" href="<?= $profileURL ?>"><i class="fas fa-user"></i>  Profile</a></li>
							<li><a class="dropdown-item" href="<?= base_url("Auth/Logout") ?>"><i class="fas fa-sign-out-alt"></i>  Logout</a></li>
						</ul>
					</li>
				<?php endif; ?>
		</div>
	</div>
</nav>

<!-- Privacy Policy Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="pdfModalLabel">Our privacy policy</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<embed src="<?= base_url("PrivacyPolicy.pdf");?>" type="application/pdf" width="100%" height="600px">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script>
	function openPrivacyPolicyModal() {
		$('#pdfModal').modal('show');
	}
	$("#privacyPolicyModalCloseButton").click(function() {
		$("#pdfModal").modal("hide");
	});

	function openPrivacyPolicy() {
		window.open("<?= base_url("Home/Index/3");?>", "_blank");
	}
</script>

