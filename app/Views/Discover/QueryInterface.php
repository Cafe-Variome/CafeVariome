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
            <h5 class="card-header border-secondary">Patient Information</h5>
            <div class="card-body">
                <div class="row" id="age">
                    <div class="col-3">Age: </div>
                    <div class="col-6">
                        <div id="age-diagnosis-range" class="mt-2"></div>
                    </div>
                    <div class="col">
                        <input type="text" id="age-diagnosis-value" readonly style="border:0; font-weight:bold;">
                    </div>
                </div><br />
                <div class="row" id="genderr">
                    <div class="col-3">Gender:</div>
                    <div class="col-9">
                        <span>
                            <label for="genmale">Male</label>
                            <input type="radio" name="genderr" id="genmale" value="Male">
                        </span>
                        <span>
                            <label for="genfemale">Female</label>
                            <input type="radio" name="genderr" id="genfemale" value="Female">
                        </span>
                        <!-- <span>
                            <label title="A proper value is applicable but not known." for="genunk">Unknown</label>
                            <input type="radio" name="genderr" id="genunk" value="0">
                        </span> -->
                        <span>
                            <label title="Search records for all values of gender." for="genany">Any</label>
                            <input type="radio" name="genderr" id="genany" value="Any" checked>
                        </span>
                    </div>
                    <div class="col"></div>
                </div>
            </div>
        </div>
        <hr />
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">SNOMED CT</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_filter_phen_left" type="text" placeholder="search SNOMED-CT by keyword" style="text-align: center;" />
                        </div>
                        <select id='values_phen_left' class="form-control" size="10"></select>
                        <button class="btnAdd btn btn-secondary btn-block">Add</button>
                    </div>
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_filter_phen_right" type="text" placeholder="search SNOMED-CT by keyword" style="text-align: center;">
                            <div class="input-group-append">
                                <div class="input-group-text btn btn-light" id="btnClr"><a type="tooltip" title="Clear all contents"><i class="fa fa-eraser"></i></a></div>
                            </div>
                        </div>
                        <select id="values_phen_right" class="form-control" size="10"></select>
                        <button class="btnRemove btn btn-secondary btn-block">Remove</button>
                    </div>
                    <div>
                        <a data-toggle="tooltip" data-placement="right" title="Diagnosis code"><i class="fa fa-question-circle text-info">&nbsp;&nbsp;</i></a>
                    </div>
                </div><br>
                <div class="row">
                    <!-- <div class="col-2 text-muted"> Date of Diagnosis:</div> -->
                    <div class="col-4">
                        <!-- <span>
                            <input type="date" disabled id="snomedDate" name="snomed-date" placeholder="Select date of diagnosis" min="1930-01-01" max="2022-12-31" style="height: 25px;">
                        </span> -->
                    </div>
                    <div class="col-2 text-right">
                        <!-- <div>
                            <a data-toggle="tooltip" data-placement="right" title="Diagnosis date"><i class="fa fa-question-circle text-info"></i></a>
                        </div> -->
                    </div>
                    <div class="col">Match terms:</div>
                    <div class="col">
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-3">
                        <!-- <select id="diagDateSelect" disabled>
                            <option value="any" selected>Anytime since last import</option>
                            <option value="6">6 months ago</option>
                            <option value="12">12 months ago</option>
                        </select> -->
                    </div>
                    <div class="col-3"></div>
                    <div class="col">
                        Any
                    </div>
                    <div class="col-4">
                        <div id="similarity-range" class="mt-2">
                            <div id="sr-handle" class="ui-slider-handle"></div>
                        </div>
                    </div>
                    <div class="col">
                        All
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-2"> Date of Diagnosis:</div>
                    <div class="col">
                        <span>
                        <input type="date" id="snomedDate" name="snomed-date" placeholder="Select date of diagnosis" min="1930-01-01" max="2022-12-31" style="height: 40px;">
                        </span>&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>
                            <label for="ddiagyes" title="Present at specified time">Present</label>
                            <input type="radio" name="diagdate" id="ddiagyes" value="present">
                        </span>
                        <span>
                            <label for="ddiagno" title="Absent at specified time">Absent</label>
                            <input type="radio" name="diagdate" id="ddiagno" value="absent">
                        </span>
                        <span>
                            <label for="ddiagany" title="Search all records">Any</label>
                            <input type="radio" name="diagdate" id="ddiagany" value="any">
                        </span>&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>
                        <select id="diagDateSelect" style="height: 40px; width: 250px;">
                            <option value="any" selected>Anytime since last import</option>
                            <option value="6">6 months ago</option>
                            <option value="12">12 months ago</option>
                        </select>
                        <a data-toggle="tooltip" data-placement="right" title="since last import"><i class="fa fa-question text-secondary">&nbsp;&nbsp;</i></a>
                        </span>
                    </div>
                </div> -->
            </div>
        </div>
        <hr />
        <div class="card border-secondary">
            <h5 class="card-header border-secondary">Medication</h5>
            <div class="card-body">
                <div class="row">
                    <!-- <div class="col-3"> Code / Term:</div>
                    <div class="col-7">
                        <select class=" form-control" multiple="multiple" tabindex="-1" id="medCodeSelect">
                            <option></option>
                        </select>
                    </div>
                    <div>
                        <a data-toggle="tooltip" data-placement="right" title="Medication term / code"><i class="fa fa-question-circle text-info">&nbsp;&nbsp;</i></a>                        
                    </div> -->
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_med_phen_left" type="text" placeholder="search medication by keyword" style="text-align: center;" />
                        </div>
                        <select id='med_values_phen_left' class="form-control" size="10"></select>
                        <button class="btnAdd2 btn btn-secondary btn-block">Add</button>
                    </div>
                    <div class="col">
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text"><i class="fa fa-search"></i></div>
                            </div>
                            <input class="form-control" id="search_med_phen_right" type="text" placeholder="search medication by keyword" style="text-align: center;">
                            <div class="input-group-append">
                                <div class="input-group-text btn btn-light" id="btnClrMed"><a type="tooltip" title="Clear all contents"><i class="fa fa-eraser"></i></a></div>
                            </div>
                        </div>
                        <select id="med_values_phen_right" class="form-control" size="10"></select>
                        <button class="btnRemove2 btn btn-secondary btn-block">Remove</button>
                        <!-- </div> -->
                    </div>

                    <div>
                        <a data-toggle="tooltip" data-placement="right" title="Medication term / code"><i class="fa fa-question-circle text-info">&nbsp;&nbsp;</i></a>
                    </div>
                </div><br>
                <div class="row">
                    <!-- <div class="col-2 text-muted"> Date of Diagnosis:</div> -->
                    <div class="col-4">
                        <!-- <span>
                            <input type="date" disabled id="snomedDate" name="snomed-date" placeholder="Select date of diagnosis" min="1930-01-01" max="2022-12-31" style="height: 25px;">
                        </span> -->
                    </div>
                    <div class="col-2 text-right">
                        <!-- <div>
                            <a data-toggle="tooltip" data-placement="right" title="Diagnosis date"><i class="fa fa-question-circle text-info"></i></a>
                        </div> -->
                    </div>
                    <div class="col">Match terms:</div>
                    <div class="col">
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-3">
                        <!-- <select id="diagDateSelect" disabled>
                            <option value="any" selected>Anytime since last import</option>
                            <option value="6">6 months ago</option>
                            <option value="12">12 months ago</option>
                        </select> -->
                    </div>
                    <div class="col-3"></div>
                    <div class="col">
                        Any
                    </div>
                    <div class="col-4">
                        <div id="similarity-range-meds" class="mt-2">
                            <div id="sr-handle-meds" class="ui-slider-handle"></div>
                        </div>
                    </div>
                    <div class="col">
                        All
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-2"> Prescribed Date:</div>
                    <div class="col">
                        <span>
                        <input type="date" id="medDate" name="med-date" placeholder="Select date of prescription" min="1970-01-01" max="2022-12-31" style="height: 40px;">
                        </span>&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>
                            <label for="prescyes" title="Present at specified time">Present</label>
                            <input type="radio" name="prescdate" id="prescyes" value="present">
                        </span>
                        <span>
                            <label for="prescno" title="Absent at specified time">Absent</label>
                            <input type="radio" name="prescdate" id="prescno" value="absent">
                        </span>
                        <span>
                            <label for="prescany" title="Search all records">Any</label>
                            <input type="radio" name="prescdate" id="prescany" value="any">
                        </span>&nbsp;&nbsp;&nbsp;&nbsp;
                        <span>
                        <select id="prescribedDateSelect" style="height: 40px; width: 250px;">
                            <option value="any" selected>Anytime since last import</option>
                            <option value="6">6 months ago</option>
                            <option value="12">12 months ago</option>
                        </select>
                        <a data-toggle="tooltip" data-placement="right" title="since last import"><i class="fa fa-question text-secondary">&nbsp;&nbsp;</i></a>
                        </span>
                    </div>
                </div> -->
            </div>
        </div>
        <!-- <hr/>
        <div class="row mb-2">
            <div class="col">
                <div class="card border-secondary">
                    <h5 class="card-header border-secondary">Include disease terms</h5>
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <select class=" form-control" multiple="multiple" tabindex="-1" id="snomedSelect">
                                    <option></option>
                                </select>
                            </div>
                            <div class="col">
                                <select class=" form-control" multiple="multiple" tabindex="-1" id="medCodeSelect">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</div>

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
<!-- Data Access Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Data Access Enquiry</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div id="searchTerms">

                </div>
                <form role="form" method="post" id="reused_form">
                    <p>
                    Enter your details below and we will get back to you soon with instructions to access this data.
                    </p>
                    <hr>
                    <div class="form-group">
                        <label for="name">
                            Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="50">

                    </div>
                    <div class="form-group">
                        <label for="email">
                            Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="name">
                            Message:</label>
                        <textarea class="form-control" type="textarea" name="message" id="message" placeholder="Your Message Here" maxlength="6000" rows="7"></textarea>
                    </div>
                    <button type="submit" class="btn btn-lg btn-success btn-block" id="btnContactUs">Send an initial data access enquiry →</button>

                </form>
                <div id="success_message" style="width:100%; height:100%; display:none; ">
                    <h3>Sent your message successfully!</h3>
                </div>
                <div id="error_message" style="width:100%; height:100%; display:none; ">
                    <h3>Error</h3>
                    Sorry there was an error sending your form.

                </div>
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
