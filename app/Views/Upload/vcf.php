<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>


<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "admin";?>">Dashboard Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>


<div id="load"></div>
<div class="row">
    <div class="col">
        <h4>Import VCF's into <?php echo $source_id; ?></h3>
        <p>Config file can be either a csv/xls/xlsx file with three columns with headers. FileName, Patient ID, Tissue.</p>
        <p> This will allow us to correctly map and classify your vcf files for reference and query purposes.</p>
    </div>
</div>

<form enctype="multipart/form-data" method="post" id="vcfinfo">
  <input type="hidden" id="source_id" value="<?php echo $source_id ?>" name="source">
  <div class="form-group">
    <div class="custom-file">
      <input type="file" class="custom-file-input"  name="config"  id="config" required>
      <label class="custom-file-label" for="customFile">Config File to describe VCF's:</label>
    </div>
  </div>
  <div class="form-group">
    <div class="custom-file">
      <input type="file" class="custom-file-input" name='userfile[]' id="vcfFile" required multiple>
      <label class="custom-file-label" for="customFile">File(s) to submit:</label>
    </div>
  </div>
  <div class="form-group row">
    <div class="col">
      <input type="submit" value="Submit" class="btn btn-primary"></input>
    </div>
  </div>

</form> 

<div id="confirmVcf" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Resolve VCF upload issues for Source: <?php echo $source_id; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>      
      </div>
      <div class="modal-body">
        <div id="variableIssue">
        </div>
        <table class="table table-bordered table-striped table-hover" id="vcfTable">
          <thead>
            <tr>
              <th>FileName</th>
              <th id="parent"><div class="ad-left">Action</div><div class="ad-right"><label id="child" onclick="checkAllToggle()"><input type="checkbox" name="applyAll" id="applyAll"> Apply to all</label></div></th>
            </tr>
          </thead>
          <tbody id="vcfGrid">
          </tbody>
        </table>
        <div class="row">
          <div class="col">
            <button class="btn btn-large btn-primary" onclick="proceedVcf()">Proceed</button>
          </div>
        </div>
      </div>
    </div>
  <div>
</div>
<?= $this->endSection() ?>