<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<div class="container" style="margin-bottom: 200px;">
    <div class="row-fluid" id="genotype_phenotype">
        <div class="span12 pagination-centered">
            <h2>Query Builder</h2><hr>

            <div class="" id="phenotypeBox">
                <div class="row-fluid">
                    <div class="span12 pagination-centered" style="">
                        <button class="btn btn-large input-block-level btn-info btn-collapse" id="isPhenotype" data-collapseStatus="false" style="text-align: left">
                            Phenotype
                            <i class="icon-chevron-left" style="float: right"></i>
                        </button>
                    </div>
                </div>

                <div class="collapse" id="phenotypeContainer" data-type='phenotype'>
                </div>
                <!-- end Phenotype -->
            </div> <!-- end Phenotype -->

            <br>

            <div class="row-fluid" id="reset_buildQuery">
                <div class="pagination-centered">
                    <a class="span2 offset4 btn btn-large" id="reset_phenotype"><i class="icon-trash"></i> Reset</a>
                    <a class="span2 btn btn-large btn-primary" id="buildQuery"><i class="icon-search"></i> Build Query</a>
                </div>
            </div> <!-- end Build Query -->

        </div> <!-- end span12 pagination-centered -->
    </div> <!-- end row-fluid -->

    <div id="waiting" style="display: none; text-align: center;">
            <br />Please wait...<br />
            <img src="<?php echo base_url("resources/images/loading.gif");   ?>" title="Loader" alt="Loader" />
    </div>
    <div id="query_result"></div>
    <input type="hidden" value="<?php echo $network_key;    ?>" id="network_key"/>

</div> <!-- end container -->

<div id="loader" style="display: none;"></div>

<?= $this->endSection() ?>
