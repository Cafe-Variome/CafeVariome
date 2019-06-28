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
    <link rel="stylesheet" href="<?php echo base_url(CSS."bootstrap.css");?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/fontawesome.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/brands.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(VENDOR . "components/font-awesome/css/solid.css"); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(CSS."/jstree/themes/default/style.css");?>" />
    <style>
    main > .container {
        padding: 80px 15px 0;
    }

    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        background-color: #f5f5f5;
    
    }

    .footer > .container {
        padding-right: 15px;
        padding-left: 15px;
    }

    code {
        font-size: 80%;
    }

    .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- TODO: Move from Cafe Variome 2 source code once necessary. Some files may not be necessary. -->
    <!-- TODO: The links to files which are moved are put above this section. -->
    <!-- TODO: Prepare css minification. -->

    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."global.css");?>" /> -->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."prettify.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."jquery-ui.css");?>" />
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."fileUploader.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."DT_bootstrap.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."jquery.cluetip.css");?>" type="text/css" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."bootstrap-arrows.css");?>" type="text/css" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."cookiecuttr.css");?>" type="text/css" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."jquery.treetable.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."jquery.treetable.theme.default.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."jquery.switchButton.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."bootstrap-editable.css");?>" type="text/css" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."spectrum.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."select2.css");?>" />-->
    <!--<link rel="stylesheet" href="<?php echo base_url(CSS."jquery.growl.css");?>" />-->

    <!-- extra CSS-->
    <?php //foreach($css as $c):?>
    <link rel="stylesheet" href="<?php //echo base_url().CSS.$c?>">
    <?php //endforeach;?>

    <!-- favicon and touch icons -->
    <?php  if ( $setting->settingData['cafevariome_central'] ): ?>
    <link rel="shortcut icon" href="<?php echo base_url(IMAGES.'ico/favicon.ico');?>" />
    <?php endif; ?>
    <link rel="apple-touch-icon" href="<?php echo base_url(IMAGES.'ico/apple-touch-icon-precompresse.png');?>" />
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo base_url(IMAGES.'ico/apple-touch-icon-57x57-precompressed.png');?>" />
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo base_url(IMAGES.'ico/apple-touch-icon-72x72-precompressed.png');?>" />
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo base_url(IMAGES.'ico/apple-touch-icon-114x114-precompressed.png');?>" />

    <script type="text/javascript">
        var baseurl = "<?php print base_url(); ?>";
        var authurl = "<?php print rtrim($setting->settingData['auth_server'],"/"); // remove trailing slash from the auth_server config variable ?>";
    </script>
    <!-- note: jstree requires the addBack function that wasn't added to jQuery until 1.8, therefore I have changed 1.7.1 to 1.8.1 (tb143) -->
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>-->

    <!--Initial step to upgrade to boostrap 4.3.1 by Mehdi Mehtarizadeh 11/6/2019 -->
    <script src="<?php echo base_url(JS."jquery-3.4.1.js");?>"></script>
    <script src="<?php echo base_url(JS."bootstrap.js");?>"></script>

    <script src="<?php echo base_url(JS."jstree/jstree.js");?>"></script>

    <!-- TODO: Move from Cafe Variome 2 source code once necessary. Some files may not be necessary. -->
    <!-- TODO: The links to files which are moved are put above this section. -->
    <!-- TODO: Prepare js minification. -->
                            
    <!--<script src="<?php echo base_url(JS."libs/underscore-1.3.1.min.js");?>"></script>-->
    <!--<script src="<?php  echo base_url(JS."plugins.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."script.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."cafevariome.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."bootbox.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery-ui.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."bootstrap-editable.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.dataTables.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."DT_bootstrap.js");?>"></script>-->
    <!--<script src="<?php //echo base_url(JS."jquery.ibutton.js");?>"></script> -->
    <!--<script src="<?php echo base_url(JS."jquery.metadata.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."spectrum.js");?>"></script> -->
    <!--<script src="<?php echo base_url(JS."jquery.cluetip.js");?>"></script>-->
    <!-- <script src="<?php echo base_url(JS."highcharts.js");?>"></script> -->
    <!--<script src="<?php echo base_url(JS."bootstrap-arrows.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.cookiecuttr.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.cookie.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.maskedinput.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.treetable.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.switchButton.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."select2.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.dataTables.delay.min.js");?>"></script>-->
    <!--<script src="<?php // echo base_url(JS."jquery.hideShowPassword.min.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."json3.js");?>"></script> -->
    <!--<script src="<?php echo base_url(JS."/tinymce/tinymce.min.js");?>"></script>-->        
    <!-- tb143 -->
    <!--<script src="<?php echo base_url(JS."phenotypeList.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."form_complete.js");?>"></script>-->
    
    <!--<script src="<?php echo base_url(JS."json3.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."jquery.fileUploader.js");?>"></script>-->
    <!--<script src="<?php echo base_url(JS."bootstrap-notify.js");?>"></script>-->
    
    <!--<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">-->
    <?php if ($setting->settingData['messaging']): ?>
        <!-- TODO: Move from Cafe Variome 2 source code once necessary. Some files may not be necessary. -->
        <!-- TODO: The links to files which are moved are put above this section. -->
		<!--<script type="text/javascript" src="<?php echo base_url(JS."jquery.tokeninput.js");?>"></script>-->
		<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url(CSS."token-input.css");?>" />-->
		<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url(CSS."token-input-facebook.css");?>" />-->
		<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url(CSS."token-input-mac.css");?>" />-->
		<!--<script type="text/javascript">
			$(document).ready(function () {
				$("#messaging-user-input").tokenInput("<?php print rtrim($setting->settingData['auth_server'],"/") . '/auth_messages/lookup_users';?>", {
					hintText: "Type a username",
					theme: "facebook",
					crossDomain: false // Setting to crossDomain to false makes it work with the cross domain CV auth server (not sure why!?)
				});
				
//				var navheight = $('#nav_container').height();
//				var padding = navheight * 1.3;
//				alert("height -> " + navheight + " padding -> " + padding);
//				alert("change to -> " + $("body").css("padding-top"));
//				$("body").css({"padding-top":padding + "px"});
			});
		</script>-->
		<?php endif; ?>
		


</head>

<!-- TODO: Body to be moved and adapted. -->
<body class="d-flex flex-column h-100">
    <header>
        <?= $this->include('partial\nav') ?>
    </header>
    
    <main role="main" class="flex-shrink-0">
        <div class="container">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <footer id="footer" class="footer mt-auto py-3">
        <div class="container">
            <span class="text-muted">Powered by CafeVariome</span>             
        </div>
    </footer>
    <!-- extra Java Script-->
    <?php foreach($javascript as $js):?>
    <script src="<?php echo base_url().JS.$js?>"></script>
    <?php endforeach;?>    
</body>
</html>