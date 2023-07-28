<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="row" id="timeoutalert" style="display:none;">
    <div class="col">
        <div class="alert alert-warning">
        Your session has timed out. You need to log in again. Please click <a href="<?= base_url('Auth/Login') ?>">here to log in</a>.
        </div>
    </div>
</div>
<!-- Patient Characteristics -->
<div class="row mb-2">
    <div class="col">
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">PATIENT CHARACTERISTICS</h5>
            <div class="card-body" id="pat_container">
                <div class="row rule mb-1">
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
                            <option value="---------------" disabled="">---------------</option>
                            <option value="dt<">&lt; (Date)</option>
                            <option value="dt>">&gt; (Date)</option>
                            <option value="dt<=">&lt;= (Date)</option>
                            <option value="dt>=">&gt;= (Date)</option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control value values_pat" style="margin-bottom:15px" tabindex="-1">
                            <option></option>
                        </select>
                    </div>
                    <div class="col">
                        <button data-rule="patient" class="btn btn-mini btn-success btn-add"><i class="fa fa-plus"></i></button>
                        <button data-rule="patient" class="btn btn-mini btn-danger btn-remove" style="display:none;"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OLD VARIANT -->
<!-- <div class="row mb-2">
    <div class="col">
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">VARIANT</h5>
            <div class="card-body" id="gen_container">
                <div class="row rule mb-1">
                    <div class="col">
                        <select class="form-control values values_assembly" tabindex="-1">
                            <option></option>
                            <option value='GRCh37' selected="">GRCh37</option>
                        </select>
                    </div>
                    <div class="col">
                        <select class="form-control values values_chr" tabindex="-1">
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
                        <button data-rule="genotype" class="btn btn-mini btn-success btn-add"><i class="fa fa-plus"></i></button>
                        <button data-rule="genotype" class="btn btn-mini btn-danger btn-remove" style="display:none;"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- NEW VARIANT -->
<div class="row mb-2">
    <div class="col">
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">VARIANT</h5>
            <div class="card-body" id="gen_container">
                <div class="row">
                    <div class="col-5">
                        <div class="row">Genes:</div>
                        <div class="row mb-5">
                            <select class="form-control" multiple="multiple" tabindex="-1" id="genes_box">
                                <option></option>
                            </select>
                        </div>
                        <div class="row">Pathways:</div>
                        <div class="row">
                            <select class="form-control" multiple="multiple" tabindex="-1" id="reactome_box">
                                <option></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="row mb-2">
                            <div class="col">
                                <b>Mutation Type:</b>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6"></div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="sall">
                                    <label class="custom-control-label" for="sall">Select All</label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Non-coding:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="ncoding">
                                    <label class="custom-control-label" for="ncoding"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Missense:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="mss">
                                    <label class="custom-control-label" for="mss"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Nonsense:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="nss">
                                    <label class="custom-control-label" for="nss"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Splice:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="splice">
                                    <label class="custom-control-label" for="splice"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Frameshift:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="frameshift">
                                    <label class="custom-control-label" for="frameshift"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Loss of Start:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="lostart">
                                    <label class="custom-control-label" for="lostart"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Loss of Stop:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="lostop">
                                    <label class="custom-control-label" for="lostop"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row ml-1">
                            <div class="col-6">Indel:</div>
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="indel">
                                    <label class="custom-control-label" for="indel"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="row">
                            <div class="col">Max. AF</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <input type="text" class="form-control" id="max_af" placeholder="">
                            </div>
                        </div>
                        <div class="row" style="display:none;">
                            <div class="col">Min. GPS</div>
                        </div>
                        <div class="row" style="display:none;">
                            <div class="col-9">
                                <input type="text" class="form-control" placeholder="">
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-secondary" data-toggle="tooltip"
                                    data-placement="top" title="Genome Pathogenecity Score">
                                    <span class="fa fa-question"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
            </div>
        </div>
    </div>
</div>


<div class="row mb-2">
    <div class="col">
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">HPO</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_filter_phen_left" type="text" placeholder="Start typing and a list of relevant items will populate... " style="text-align: center;" />
                        </div>
                        <select id='values_phen_left' class="form-control" size="10"></select>
                        <button class="btnAdd btn btn-secondary btn-block">Add</button>
                    </div>
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_filter_phen_right" type="text" placeholder="filter by keyword" style="text-align: center;">
                        </div>
                        <select id="values_phen_right" class="form-control" size="10"></select>
                        <button class="btnRemove btn btn-secondary btn-block">Remove</button>
                    </div>
                </div>
                <hr/>
                <div class="row mb-3">
                    <div class="col-4">HPO Term Pairwise Similarity:</div>
                    <div class="col-2 text-right">
                        Minimum
                    </div>
                    <div class="col-4">
                        <div id="similarity-rel-range" class="mt-2">
                            <div id="srr-handle" class="ui-slider-handle"></div>
                        </div>
                    </div>
                    <div class="col-2">
                        Exact
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">Minimum Matched Terms:</div>
                    <div class="col-2 text-right">
                        Any
                    </div>
                    <div class="col">
                        <div id="similarity-range" class="mt-2">
                            <div id="sr-handle" class="ui-slider-handle"></div>
                        </div>
                    </div>
                    <div class="col-2">
                        All
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-5">
                        Include ORPHA:
                    </div>
                    <div class="col-5">
                        <input type="checkbox" class="custom-control-input" id="includeORPHA">
                        <label class="custom-control-label" for="includeORPHA"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ORDO -->

<div class="row mb-2">
    <div class="col">
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">ORDO</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col-1 pt-2"> ORDO:</div>
                    <div class="col-11">
                            <select class=" form-control" multiple="multiple" tabindex="-1" id="ordoSelect">
                            <option></option>
                        </select>
                    </div>
                </div>
                <hr/>
                <div class="row mb-3">
                    <div class="col-4">HPO Term Pairwise Similarity:</div>
                    <div class="col-2 text-right">
                        Minimum
                    </div>
                    <div class="col-4">
                        <div id="similarity-rel-range-ordo" class="mt-2">
                            <div id="srr-handle" class="ui-slider-handle"></div>
                        </div>
                    </div>
                    <div class="col-2">
                        Exact
                    </div>
                </div>
                <hr/>
                <div class="row mb-3">
                    <div class="col-4">ORDO Match Scale</div>
                    <div class="col-2 text-right">
                        Minimum
                    </div>
                    <div class="col-4">
                        <div id="match-scale-ordo" class="mt-2">
                            <div id="srr-handle" class="ui-slider-handle"></div>
                        </div>
                    </div>
                    <div class="col-2">
                        Exact
                    </div>
                </div>
                <hr/>
                <div class="row mb-3">
                    <div class="col-5">
                        Include HPO:
                    </div>
                    <div class="col-5">
                        <input type="checkbox" class="custom-control-input" id="includeHPO">
                        <label class="custom-control-label" for="includeHPO"></label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Phenotype and HPO Tree -->
<!-- <div class="row mb-2">
    <div class="col">
        <div class="card">
            <h5 class="card-header">Phenotype</h5>
            <div class="card-body" id="phen_container">
                <div class="row rule">
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_filter_phen_left" type="text" placeholder="filter by keyword" style="text-align: center;" />
                        </div>
                        <select id='values_phen_left' class="form-control" size="10"></select>
                        <button class="btnAdd btn btn-secondary btn-block">Add</button>
                    </div>
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_filter_phen_right" type="text" placeholder="filter by keyword" style="text-align: center;">
                        </div>
                        <select id="values_phen_right" class="form-control" size="10"></select>
                        <button class="btnRemove btn btn-secondary btn-block">Remove</button>
                    </div>
                </div>
                <hr/>
                <div class="row rule">
                    <div class="col-10">
                        <h4 style="font-weight: bold; text-align: center;">HPO Tree </h4>
                        <a id='full_screen' style="float: right; margin-left: 0px;" href="" class="btn btn-info">
                            <i class="fa fa-window-restore"></i>
                        </a>
                        <div id="jstree_hpo" style="max-height: 400px; overflow: scroll; border: 1px dotted; border-radius: 5px;"></div>
                    </div>
                    <div class="col-2">
                        <div id="phen_logic">
                            <a class="btn btn-logic btn-block btn-medium btn-primary active">AND</a>
                            <a class="btn btn-logic btn-block btn-medium btn-secondary">OR</a>
                            <a class="btn btn-logic btn-block btn-medium btn-secondary">SIM</a>
                        </div>
                        <label class="checkbox inline">
                            <input type="checkbox" id="rel" value="rel" checked disabled> Rel
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
    </div>
</div> -->

<input type="hidden" value="<?php echo $network_key;?>" id="network_key"/>
<input type="hidden" value="<?php echo $user_id;?>" id="user_id"/>
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

<div class="row" id="reset_buildQuery">
    <div class="col">
        <a class="btn btn-lg btn-primary<?= ($elasticSearchEnabled ? "" : " disabled"); ?>" id="build_query"><i class="fa fa-search"></i> Build Query</a>
		<button class="btn btn-lg btn-warning" id="cancel_query" style="display: none;"><i class="fa fa-times"></i> Cancel</button>
		<a class="btn btn-secondary btn-lg" id="reset_query"><i class="fa fa-trash"></i> Reset</a>
    </div>
</div>
<div class="row">
	<div class="col">
		<span class="text-danger" id="query_error"></span>
	</div>
</div>

<hr/>
<!-- Loader -->
<div id="waiting" style="text-align: center;display:none;">
<br />Searching...<br />
<img src="<?php echo base_url("resources/images/loading.gif");   ?>" title="Loader" alt="Loader" />
</div>

<!-- Result Table -->
<table id="query_result" class="table table-hover table-bordered table-striped" style="display:none;">
    <thead>
        <tr>
            <th>Source Name</th>
            <th>Results</th>
			<th>Source Details</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<!-- Full Screen HPO Tree Modal -->
<div class="modal fade" id="hpoTreeModal" tabindex="-1" role="dialog" aria-labelledby="hpoTreeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">HPO Tree</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="jstreeArea">

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resultModalLabel">Results</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
		  <div class="row">
			  <div class="col text-center">
				  <div class="spinner-grow text-info" role="status" style="width: 5rem; height: 5rem; display:none" id="loader">
					  <span class="sr-only">Loading...</span>
				  </div>
			  </div>
		  </div>
        <table class="table table-hover table-stripped table-bordered" id="resTbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject ID</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Source Modal -->
<div class="modal fade" id="sourceModal" tabindex="-1" role="dialog" aria-labelledby="sourceModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="sourceModalLabel">Source Information</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-6">
						<b>Source Name</b>
					</div>
					<div class="col-6">
						<b>Owner Name</b>
					</div>
				</div>
				<div class="row">
					<div class="col-6" id="source_name">

					</div>
					<div class="col-6" id="source_owner">

					</div>
				</div>
				<div class="row">
					<div class="col-6">
						<b>Owner Email</b>
					</div>
					<div class="col-6">
						<b>Source URL</b>
					</div>
				</div>
				<div class="row">
					<div class="col-6" id="source_owner_email">

					</div>
					<div class="col-6" id="source_uri">

					</div>
				</div>
				<div class="row">
					<div class="col-6">
						<b>Short Description</b>
					</div>
				</div>
				<div class="row">
					<div class="col-12" id="source_description">

					</div>
				</div>
				<div class="row">
					<div class="col-6">
						<b>Full Description</b>
					</div>
				</div>
				<div class="row">
					<div class="col-12" id="source_long_description">

					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<?= $this->endSection() ?>
