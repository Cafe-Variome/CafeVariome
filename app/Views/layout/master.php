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
    <title><?php echo $site_title ?> | <?php echo $title ?></title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="<?php echo $meta_keywords ?>" />
    <meta name="author" content="<?php echo $meta_author ?>" />
    <meta name="description" content="<?php echo $meta_description ?>" />

    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "twbs/bootstrap/dist/css/bootstrap.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(CSS . "site.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/fontawesome.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/brands.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/solid.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "select2/select2/dist/css/select2.css"); ?>" />


    <!-- extra CSS-->
    <?php foreach ($css as $c) : ?>
        <link rel="stylesheet" href="<?php echo base_url($c) ?>">
    <?php endforeach; ?>

    <!-- favicon and touch icons -->
    <link rel="shortcut icon" href="<?php echo base_url(IMAGES . 'logos/favicon.ico'); ?>" />

    <script type="text/javascript" src="<?= base_url('UserInterfaceAPI/GetUIConstants') ?>"></script>

    <script src="<?php echo base_url(JS . "jquery-3.6.0.min.js"); ?>"></script>


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
                            | <a href="#" data-toggle="modal" data-target="#privacyPolicyModal">Privacy Policy</a>
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
