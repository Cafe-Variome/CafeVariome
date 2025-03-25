<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <div class="container-fluid">
    <div class="navbar-brand">
        <a href="<?php echo base_url(); ?>">
            <img src="<?= base_url("resources/images/logos/lehmrLogo.png")?>" class="logo" alt="LeHMR">
        </a>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent" style="max-width: 510px;margin-left: auto; min-width: 300px;">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0" style="width: -webkit-fill-available !important;">
        <?php $request = service('request'); ?>
        <li class="nav-item">
            <a id ="home" class="nav-link" href="https://lehmr.le.ac.uk/"> Home</a>
        </li>
        <li class="nav-item">
            <a id ="adddata" class="nav-link" href="https://lehmr.le.ac.uk/Getdata/index">Add Data</a>
        </li>
        <li class="nav-item dropdown">
            <a id ="editdata" class="nav-link" href="https://lehmr.le.ac.uk/editdata">Edit Data</a>
        </li>
        <li class="nav-item active">
            <a  id ="explore" class="nav-link" href="<?php echo base_url() ?>">Explore Datasets</a>
        </li>
      </ul>
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

