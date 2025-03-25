<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>


<div class="MidContainer mx-auto">
    <div class="multi_step_form">
        <span id="requestAccessForm" class="msform">
            <fieldset  class="mt-1 formFeildset">
                <div class="tittle text-center mb-0">
                    <h4 class="mb-0 text-center">LeHMR Query Builder</h4>
                </div>
                <hr>
                <div id="userinformation">
                    <div class="row mt-3 p-0">
                        
                        <div class="form-group col-md-6 col-6 col-12">
                            
                            <span class = "input-group" >
                            <label for="u_fname" class="nl" >Dataset Title or Keyword:</label>
                              <select name="d_datatitle" class="form-control"  id="d_datatitle" multiple="multiple">
                                <?php foreach ($titles as $title): ?>
                                  <option value="<?= $title; ?>" ><?= $title; ?></option>
                                 
                                <?php endforeach; ?>
                                <!-- keywords -->
                                <?php foreach ($keywords as $k): ?>
                                  <option value="<?= $k; ?>" ><?= $k; ?></option>
                                <?php endforeach; ?>
                              </select>
                            </span>
    
                        </div>
                        <div class="form-group col-md-6  col-12">
                          <span class="input-group">
                            <label for="d_datatype" class="nl" >Data Types:</label>
                            <select name="d_datatype" id="d_datatype" class="form-controle" multiple="multiple">
                              <option></option>
                            </select>
                          </span>
                        </div>
                    </div>
                    <div class="row mt-1 p-0">
                        <div class="form-group col-md-6 col-12">

                          <span class="input-group">
                            <label for="d_datatheme" class="nl" >Theme or Department:</label>
                            <select name="d_datatheme" id="d_datatheme" multiple="multiple" class="form-controle" >
                              <option></option>
                              <option>Cardiovascular</option>
                              <option>Lifestyle</option>
                              <option>Respiratory</option>
                            </select>
                          </span>

                        </div>
                        <div class="form-group col-lg-6 col-md-6 col-sm-12 col-12">
                          <label for="d_studysize">Minimum Study Size:</label>
                          <input type="number" name="d_studysize" id="d_studysize" min="0" class="form-control" placeholder="Study Size">
                        </div>
                    </div>
                </div>
                <hr>
                <button id="build_query" class="btn action-button"><i class="bi bi-search"></i> Search </button>
                <button id="reset_query" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i> Reset </button>
            </fieldset>
          </span>

    </div>


    <fieldset  class="mt-5 resulttable">
      <div class="tittle text-center mb-0">
          <h4 class="mb-0 text-center">LeHMR Datasets</h4>
          <!-- <small class="text-muted">An access link to your datasets will be sent to the email you used when adding the dataset.</small> -->
      </div>
      <hr>
      <!-- Table for displaying datasets with DataTables -->
      <table id="datasetTable" class="table table-striped table-bordered table-responsive">
      <thead>
        <tr>
          <th>Title</th>
          <th>Abstract</th>
          <th>Data Types</th>
          <th>Contact Point</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>

      </tbody>
      </table>
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
    </fieldset>


</div>



















<!-- View Dataset Modal -->
<div class="modal fade" id="viewDatasetModal" tabindex="-1" aria-labelledby="viewDatasetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="viewDatasetModalLabel">Dataset Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        <!-- Dataset Information Section -->
        <div class="card mb-3">
          <a class="btn btn-secondary">
            <div class="card-header">
              <h6 class="mb-0">Dataset Information</h6>
            </div>
          </a>
          <div class="card-body">
            <table class="table table-bordered">
              <tbody>
                <!-- Include all dataset fields here -->
                <tr>
                  <th>Title:</th>
                  <td id="datasetTitle"></td>
                </tr>
                <tr>
                  <th>Abstract:</th>
                  <td id="datasetAbstract"></td>
                </tr>
                <tr>
                  <th>Research Study:</th>
                  <td id="researchStudy"></td>
                </tr>
                <tr>
                  <th>Data Types:</th>
                  <td id="dataTypes"></td>
                </tr>
                <tr>
                  <th>Ethnicities:</th>
                  <td id="ethnicities"></td>
                </tr>
                <tr>
                  <th>Funders:</th>
                  <td id="funders"></td>
                </tr>
                <tr>
                  <th>Geographies:</th>
                  <td id="geographies"></td>
                </tr>
                <tr>
                  <th>Keywords:</th>
                  <td id="keywords"></td>
                </tr>
                <tr>
                  <th>Age Range:</th>
                  <td id="ageRange"></td>
                </tr>
                <tr>
                  <th>Study Size:</th>
                  <td id="studySize"></td>
                </tr>
                <tr>
                  <th>Data Controller:</th>
                  <td id="dataController"></td>
                </tr>
                <tr>
                  <th>Access Rights:</th>
                  <td id="accessRights"></td>
                </tr>
                <tr>
                  <th>Legal Jurisdiction:</th>
                  <td id="legalJurisdiction"></td>
                </tr>
                <tr>
                  <th>Organisation:</th>
                  <td id="organisation"></td>
                </tr>
                <tr>
                  <th>Contact Point:</th>
                  <td id="contactPoint"></td>
                </tr>
                <tr>
                  <th>HDR Consent:</th>
                  <td id="hdrConsent"></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Publications Section -->
        <!-- Each publication card will be added here dynamically -->
        <div class="card mb-3" id="publicationTemplate" style="display:none;">
            <div class="card-header">
              Publication <span class="publication-number"></span>: <span class="publication-title"></span>
            </div>
            <div class="card-body">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th>Journal Name:</th>
                    <td class="publication-venue"></td>
                  </tr>
                  <tr>
                    <th>First Author:</th>
                    <td class="publication-author"></td>
                  </tr>
                  <tr>
                    <th>Publication Year:</th>
                    <td class="publication-year"></td>
                  </tr>
                  <tr>
                    <th>DOI:</th>
                    <td class="publication-doi"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        <!-- Publication Template -->
        <div class="card mb-3">
          <a href="#publicationsCollapse" class="btn btn-primary" data-bs-toggle="collapse">
            <div class="card-header">
              <h6 class="mb-0">Publications</h6>
            </div>
          </a>
          <div id="publicationsCollapse" class="collapse">
            <div class="card-body">
              <div id="publicationsSection"></div>
            </div>
          </div>
        </div>

        <!-- Researchers Section -->
        <!-- Researcher Template -->
        <div class="card mb-3" id="researcherTemplate" style="display:none;">
          <div class="card-header">
              Researcher <span class="researcher-number"></span> : <span class="researcher-title"></span> <span class="researcher-name"></span>
          </div>
          <div class="card-body">
              <table class="table table-bordered">
                  <tbody>
                      <tr>
                          <th>Email:</th>
                          <td class="researcher-email"></td>
                      </tr>
                      <tr>
                          <th>Affiliations:</th>
                          <td class="researcher-affiliations"></td>
                      </tr>
                      </tbody>
              </table>
          </div>
        </div>

        <div class="card mb-3">
          <a href="#researchersCollapse"  class="btn btn-success" data-bs-toggle="collapse">
            <div class="card-header">
              <h6 class="mb-0">Researchers</h6>
            </div>
          </a>
          <div id="researchersCollapse" class="collapse ">
            <div class="card-body">
                <div id="researchersSection"></div>
            </div>
          </div>
        </div>

        <!-- Conditions Section -->
        <div class="card">
          <a href="#conditionsCollapse" class="btn btn-warning" data-bs-toggle="collapse" >
              <div class="card-header">
                <h6 class="mb-0">Conditions</h6>
              </div>
          </a>
          <div id="conditionsCollapse" class="collapse">
            <div class="card-body">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th>Allowed Countries:</th>
                    <td id="allowedCountries"></td>
                  </tr>
                  <tr>
                    <th>Profit Use:</th>
                    <td id="profitUse"></td>
                  </tr>
                  <tr>
                    <th>Broad Research Use:</th>
                    <td id="broadResearchUse"></td>
                  </tr>
                  <tr>
                    <th>Specific Research Use:</th>
                    <td id="specificResearchUse"></td>
                  </tr>
                  <tr>
                    <th>Recontact:</th>
                    <td id="recontact"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


   <!-- Initialize DataTables with server-side processing -->
   <script>
        $(document).ready(function() {

        });
    </script>
<?= $this->endSection() ?>
