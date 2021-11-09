<?php

/**
    *@author Mehdi Mehtarizadeh
    *
    *This is the master layout for all pages.
*/

?>
<!doctype html>
<html class="h-100">
<head>
    <title><?php echo $setting->settingData['site_title'] ?> | <?php echo $title ?></title>

    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="keywords" content="<?php echo $keywords ?>" />
	<meta name="author" content="<?php echo $author ?>" />
	<meta name="description" content="<?php echo $description ?>" />

    <!-- Initial step to upgrade to boostrap 4.3.1 by Mehdi Mehtarizadeh 11/6/2019 -->
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "twbs/bootstrap/dist/css/bootstrap.css");?>" />
    <link rel="stylesheet" href="<?php echo base_url(CSS . "site.css");?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/fontawesome.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/brands.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/solid.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "select2/select2/dist/css/select2.css");?>" />


    <!-- extra CSS-->
    <?php foreach($css as $c):?>
    <link rel="stylesheet" href="<?php echo base_url($c) ?>">
    <?php endforeach;?>

    <!-- favicon and touch icons -->
    <link rel="shortcut icon" href="<?php echo base_url(IMAGES.'logos/favicon.ico');?>" />

    <script type="text/javascript">
        var baseurl = "<?= base_url(); ?>" + '/';
        var authurl = "<?php print rtrim($setting->settingData['auth_server'],"/"); // remove trailing slash from the auth_server config variable ?>";
    </script>
    <script src="<?php echo base_url(JS."jquery-3.6.0.min.js");?>"></script>

</head>

<body class="d-flex flex-column h-100">
    <div class="container">
        <header>
            <?= $this->include('partial/nav') ?>
        </header>
    </div>
    <main role="main">
        <div class="container">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <footer id="footer" class="footer <?= ($stickyFooter) ? 'footer-sticky' : '' ?> mt-auto py-3">
        <div class="container">
            <span class="text-muted">Powered by CafeVariome</span>
        </div>
    </footer>

    <script src="<?php echo base_url(VENDOR."twbs/bootstrap/dist/js/bootstrap.bundle.js");?>"></script>
    <script src="<?php echo base_url(VENDOR."select2/select2/dist/js/select2.js");?>"></script>
    <script type="text/javascript">
        $('[data-toggle="tooltip"]').tooltip();
    </script>

    <!-- extra Java Script-->
    <?php foreach($javascript as $js):?>
    <script src="<?php echo (substr($js, 0, 4) != 'http' ? base_url($js) : $js)?>"></script>
    <?php endforeach;?>
</body>
</html>
