<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
	<div class="container">
		<!--<div class="container-fluid">-->
		<div class="row-fluid">
			<div class="span12 pagination-centered">
				<div class="well">
					<h3>Advertise Your Data Via Cafe Variome "Central"</h3>
					<p>Cafe Variome "Central" (available at <a href="http://www.cafevariome.org" >http://www.cafevariome.org</a>) was originally designed to function as a clearinghouse and exchange portal for gene variant (mutation) data produced by diagnostics laboratories and all available variants from public repositories, offering users a portal through which to announce, discover and acquire a comprehensive listing of observed neutral and disease-causing gene variants in patients and unaffected individuals.</p>
					<p>Cafe Variome can help you publish your data and share with others in a number of different ways:</p>
					<?php //echo br(2); ?><br/><br/>
					<?php echo img("resources/images/cafevariome/labarrow.png"); ?>
				</div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span10 offset1">
				<div class="well">
					<div class="pagination-centered">
						<h4>A. Diagnostic Software</h4>
						<?php echo img("resources/images/cafevariome/diag_submit.png"); ?>
						<br />
					</div>
					<p>Cafe Variome has worked closely with the providers of mutation interpretation software used in diagnostic laboratories to produce versions of these tools that will allow you to submit sequence variants directly to a Cafe Variome installation.</p>
					<div class="pagination-centered">	
						<p>The following software now offers this functionality:</p>
						<p>- <strong>Gensearch</strong> (PhenoSystems). See the <?php echo anchor('about/gensearch', 'following page', 'title="Gensearch Instructions"'); ?> for details of how to submit.</p>
						<p>- <strong>Alamut</strong> (Interactive Biosoftware). See the <?php echo anchor('http://www.interactive-biosoftware.com/alamut/doc/2.1/reporting.html', 'following page', 'title="Alamut Instructions"'); ?> for details of how to submit.</p>
					</div>
					<p>Providers of other diagnostic software are encouraged to <?php echo mailto("admin@cafevariome.org", "contact us"); ?> to discuss enabling similar functionality in their own tools.</p>
				</div>
			</div><!--/span-->
		</div>
		<div class="row-fluid">
			<div class="span10 offset1">
				<div class="well">
					<div class="pagination-centered">
						<h4>B. Upload to Cafe Variome "Central"</h4>
						<?php echo img("resources/images/cafevariome/upload.png"); ?>
						<br />
					</div>
					<p>If you do not use software that is currently supported by Cafe Variome or you wish to upload variants in bulk you may use the built in data import facility to import variants.</p>
					<p>Variants can be added through the curators interface of Cafe Variome, <?php echo mailto("admin@cafevariome.org", "contact us"); ?> in order to have your account set as a curator.</p>
				</div>
			</div><!--/span-->
		</div>
		<div class="row-fluid">
			<div class="span10 offset1">
				<div class="well">
					<div class="pagination-centered">
						<h4>C. Hosted and "in-a-box" versions</h4>
						<?php echo img("resources/images/cafevariome/solutions.png"); ?>
						<br />
					</div>
					<p>We recognise that some users may not wish to use the central version of Cafe Variome and instead create their own private installations. In order to facilitate this we offer both a fully hosted version (on University of Leicester servers) and an in-a-box version to interested collaborators.</p>
					<div class="pagination-centered"><p><?php echo mailto("admin@cafevariome.org", "Contact us"); ?> to discuss either of these options.</p></div>
				</div>
			</div><!--/span-->
		</div>

		<hr>
	</div><!--/.fluid-container-->
<?= $this->endSection() ?>