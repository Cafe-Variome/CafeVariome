<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>

<div id="load"></div>
<div class="container">
  <div class="row-fluid">
    <div class="span9 offset3">
      <div class="well">
        <h3>Import VCF's for <?php echo $source; ?></h3>
        <p>Config file can be either a csv/xls/xlsx file with three columns with headers. FileName, Patient ID, Tissue.</p>
        <p> This will allow us to correctly map and classify your vcf files for reference and query purposes.</p> <!-- -->
        <div id="vcf_errors"></div>
        <form enctype="multipart/form-data" method="post" id="vcfinfo">
           <p>
            <label>Config File to describe VCF's:</label>
          <input type='file' name="config" style="line-height: normal;" required>
          </p>
          <p></p>
          <p>
          <label>File/Files to submit:</label>
          <input type='file' name='userfile[]' style="line-height: normal;" required multiple/></input> 
          </p>
          <p></p>
          <p>
          <input type="hidden" id="source" value="<?php echo $source ?>" name="source">
          <input type="submit" value="Submit"></input>
          </p>
        </form> 
        <br>
        <br>         
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>