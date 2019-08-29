<?= $this->extend('layout\master') ?>
<?= $this->section('content') ?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  <li class="breadcrumb-item"><a href="<?php echo base_url() . "hme";?>">Dashboard Home</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= $title ?></li>
  </ol>
</nav>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>	
	</div>	
</div>
<hr>

<div class="row mb-2">
    <div class="col">
        <div class="card">
            <h5 class="card-header">PATIENT CHARACTERISTICS</h5>
            <div class="card-body" id="pat_container">
                <div class="row rule">
                    <div class="col">
                        <select class="form-control attribute keys_pat" style="margin-bottom:15px" tabindex="-1">
                            <option></option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control conditions" tabindex="-1">
                            <option></option>
                            <option value="is">IS</option>
                            <option value="is like">IS LIKE</option>
                            <option value="is not">IS NOT</option>
                            <option value="is not like">IS NOT LIKE</option>
                            <option value="---------------" disabled="">---------------</option>
                            <option value="=">=</option>
                            <option value="!=">≠</option>
                            <option value="<">&lt;</option>
                            <option value=">">&gt;</option>
                            <option value="<=">&lt;=</option>
                            <option value=">=">&gt;=</option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control value values_pat" style="margin-bottom:15px" tabindex="-1">
                            <option></option>
                        </select>
                    </div>
                    <div class="col">
                        <button data-rule="patient" class="btn btn-mini btn-success btn-add"><i class="fa fa-plus"></i></button>
                        <button data-rule="patient" class="btn btn-mini btn-danger btn-remove"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</div>

<div class="row mb-2">
    <div class="col">
        <div class="card">
            <h5 class="card-header">VARIANT</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <select class="form-control values values_assembly" tabindex="-1">
                            <option></option>
                            <option value='GRCh37' selected="">GRCh37</option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control values" id="values_chr" tabindex="-1">
                            <option></option>
                        </select>
                    </div>
                    <div class="col">
                        <input type="text" class="form-control values_start" placeholder="Chr start" value="42929130">
                    </div>
                    <div class="col">
                        <input type="text" class="form-control values_end" placeholder="Chr end" value="42929131">
                    </div>
                    
                    <div class="col">
                        <select class="form-control values_refall" style="margin-bottom:15px" tabindex="-1">
                            <option></option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control values_altall" style="margin-bottom:15px" tabindex="-1">
                            <option></option>
                        </select>
                    </div>
                    <div class="col">
                        <button data-rule="genotype" class="btn btn-small btn-success"><i class="fa fa-plus"></i></button>
                        <button data-rule="genotype" class="btn btn-small btn-danger"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</div>

<input type="hidden" value="<?php echo $network_key;?>" id="network_key"/>


<div class="row rules" id="genotype_phenotype">
        <div class="span12">

            <div class="" id="genotypeBox" style="margin-top: 20px;">
                <div class="collapse" id="gen_container" data-type='genotype'>

                </div>
                <!-- end genotype -->
            </div>
            <!-- overflow: auto; width: 300px; -->
            <div class="" id="phenotypeBox" style="margin-top: 50px;display:none;">
                <div class="row-fluid">
                    <div class="span12 " style="">
                        <button class="btn btn-large input-block-level btn-success btn-collapse" id="isPhenotype" data-collapseStatus="false" style="text-align: left">
                        Phenotype
                        <i class="icon-chevron-left" style="float: right"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="phen_container" data-type='phenotype'>
                    <div class="row-fluid rule">
                        <div class="pagination-centered span4" style="margin-left: 138px;">
                            <div class="input-append" style="margin-bottom: -1px;">
                                <input class="span11" id="search_filter" type="text" placeholder="filter by keyword" style="text-align: center;">
                                <span class="add-on"><i class="icon-search"></i></span>
                            </div>
                        <select id='values_phen_left' class="input-xlarge" size="10" style="overflow: auto; width: 300px; margin-bottom: 0px"></select>
                        <button class="btnAdd btn btn-block">Add</button><br>
                    </div>
                    <div class="pagination-centered span4" style="">
                        <div class="input-append" style="margin-bottom: -1px;">
                            <input class="span11" id="search_filter2" type="text" placeholder="filter by keyword" style="text-align: center;">
                            <span class="add-on"><i class="icon-search"></i></span>
                        </div>
                    <select id="values_phen_right" class="input-xlarge" size="10" style="overflow: auto; width: 300px; margin-bottom: 0px"></select>
                    <button class="btnRemove btn btn-block">Remove</button>
                </div>
            </div>
            <div class="row-fluid rule" style="display:none;">
                <div class="span10" style="">
                    <h4 style="font-weight: bold; text-align: center;">HPO Tree </h4>
                    <a id='full_screen' style="float: right; margin-left: 0px;" href="" class="btn btn-info"><i class="icon-resize-full"></i></a>
                    <div id="jstree_hpo" style="max-height: 400px; overflow: scroll; margin-left: 15%; border: 1px dotted; border-radius: 5px;"></div>
                    <!-- <div id="jstree_hpo"></div> -->
                </div>
                <div class="span1" id="phen_logic" style="margin-left: 40px;">
                    <a class="btn btn-logic btn-block btn-medium btn-primary active">AND</a>
                    <a class="btn btn-logic btn-block btn-medium btn-default">OR</a>
                    <a class="btn btn-logic btn-block btn-medium btn-default">SIM</a><br>
                    
                    <label class="checkbox inline">
                        <input type="checkbox" id="rel" value="rel"> Rel
                    </label>
                    <input type="text" class="form-control input-mini" id="r" placeholder="" value="0.7">
                    <input type="text" class="form-control input-mini" id="s" placeholder="" value="0">
                    
                    <label class="checkbox inline">
                        <input type="checkbox" id="jc" value="jc"> Jaccard
                    </label>
                    <input type="text" class="form-control input-mini" id="j" placeholder="" value="0">
                </div>
            </div>
        </div>
    </div>
    <!-- end Phenotype Box -->

</div>
</div>

<div class="row" id="reset_buildQuery">
    <div class="col">
        <a class="btn btn-secondary  btn-lg" id="reset_query"><i class="fa fa-trash"></i> Reset</a>
        <a class="btn btn-lg btn-primary" id="build_query"><i class="fa fa-search"></i> Build Query</a>
    </div>
</div>

<hr/>

<table id="query_result" class="table table-hover table-bordered table-striped">
    <thead>
        <tr>
            <th>Source</th>
            <th>Counts</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

    
<?= $this->endSection() ?>
