<?php

/**
 *@author Mehdi Mehtarizadeh
 *@author Farid Yavari Dizjikan
 *
 *This is the master layout for all pages.
 */

?>
<!doctype html>
<html class="h-100">

<head>
    <title>LeHMR Discovery</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <!-- favicon and touch icons -->
    <link rel="shortcut icon" href="<?php echo base_url(IMAGES . 'logos/favicon.ico'); ?>" />

    <script type="text/javascript" src="<?= base_url('UserInterfaceAPI/GetUIConstants') ?>"></script>

    <script src="<?php echo base_url(JS . "jquery-3.6.0.min.js"); ?>"></script>

    <link rel="stylesheet" href="<?php echo base_url(CSS . "site.css"); ?>?v=<?php echo rand()?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(CSS . 'nav.css') ?>?v=<?php echo rand()?>"> 
    <!-- query_builder.css -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(CSS . ' query_builder.css') ?>?v=<?php echo rand()?>"> 


    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/fontawesome.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/brands.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/solid.css"); ?>" />

   
    <!-- Bootstrap -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Select 2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Swal Files -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script>var base_url = '<?php echo base_url() ?>';</script>

    <!-- DataTable -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- extra CSS-->


    <?php foreach ($css as $c) : ?>
        <link rel="stylesheet" href="<?php echo base_url($c) ?>?v=<?php echo rand()?>">
    <?php endforeach; ?>




</head>

<body class="d-flex flex-column h-100">
    <div class="container">
        <header>
            <?= $this->include('partial/nav') ?>
        </header>
    </div>
    <main role="main">
        <div class="content container">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <footer id="footer" class="footer <?= ($stickyFooter) ? 'footer-sticky' : '' ?> mt-auto py-3">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                        Powered by <a target="_blank" href="https://www.cafevariome.org/">Café Variome </a>
                        <!-- If user is logged in, show the privacy policy in footer. Else this modal is shown in the top nav bar to be easily accessible (i.e., when user is not logged in) -->
                        <?php if ($loggedIn) : ?>
                            | <a href="#" onclick="openPrivacyPolicyModal()">Privacy Policy</a>
                        <?php endif; ?>
                        <br> By <a target="_blank" href="https://le.ac.uk/health-data-research">Bioinformatics and Health Data Science Group</a>
                </div>
            </div>
			<div class="row">
				<div class="col text-center">
					Copyright &copy; <?= date("Y") ?>, <a target="_blank" href="https://le.ac.uk">University of Leicester</a>
				</div>
			</div>
        </div>
    </footer>

    <script src="<?php echo base_url(VENDOR . "twbs/bootstrap/dist/js/bootstrap.bundle.js"); ?>"></script>
    <script src="<?php echo base_url(VENDOR . "select2/select2/dist/js/select2.js"); ?>"></script>
	<script type="text/javascript">
		const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
		const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
	</script>

    <!-- extra Java Script-->
    <?php foreach ($javascript as $js) : ?>
        <script src="<?php echo (substr($js, 0, 4) != 'http' ? base_url($js) : $js) ?>"></script>
    <?php endforeach; ?>
</body>
<script>
    $(document).on('show.bs.modal', '.modal', function() {
        $(this).appendTo('body');
    });
</script>

</html>
