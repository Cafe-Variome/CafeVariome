$(function(){
    // Reset Query Builder
    $('#reset_query').click(function() {
        location.reload();
    });

    $(document).on('click', '.view-btn', function () {
        var datasetId = $(this).data('id');
    
        // Perform an AJAX request to fetch the dataset details
        $.ajax({
            url: base_url + '/lvm',
            method: 'POST',
            data: { id: datasetId },
            success: function (response) {
                if (response.success) {
                    // Populate Dataset Information
                    $('#datasetTitle').text(response.data.d_title || 'N/A');
                    $('#datasetAbstract').text(response.data.d_abstract || 'N/A');
                    $('#researchStudy').text(response.data.d_researchstudy || 'N/A');
                    $('#dataTypes').text(response.data.d_datatypes || 'N/A');
                    $('#ethnicities').text(response.data.d_ethnicities || 'N/A');
                    $('#funders').text(response.data.d_funders || 'N/A');
                    $('#geographies').text(response.data.d_geographies || 'N/A');
                    $('#keywords').text(response.data.d_keywords || 'N/A');
                    $('#ageRange').text(response.data.d_agerange || 'N/A');
                    $('#studySize').text(response.data.d_studysize || 'N/A');
                    $('#dataController').text(response.data.d_controler || 'N/A');
                    $('#accessRights').text(response.data.d_arights || 'N/A');
                    $('#legalJurisdiction').text(response.data.d_legaljurisdiction || 'N/A');
                    $('#organisation').text(response.data.d_organisation || 'N/A');
                    $('#contactPoint').text(response.data.d_conpoint || 'N/A');
                    $('#hdrConsent').text(response.data.d_hdrconsent == 1 ? 'Yes' : 'No');
    
                    // Handle Publications Section
                    if (response.data.publications && response.data.publications.length > 0) {
                        $('#publicationsSection').empty();
                        response.data.publications.forEach(function (publication, index) {
                            var publicationCard = $('#publicationTemplate').clone().removeAttr('id').show();
                            publicationCard.find('.publication-number').text(index + 1);
                            publicationCard.find('.publication-title').text(publication.pub_title || 'N/A');
                            publicationCard.find('.publication-venue').text(publication.pub_venue || 'N/A');
                            publicationCard.find('.publication-author').text(publication.pub_author || 'N/A');
                            publicationCard.find('.publication-year').text(publication.pub_date || 'N/A');
                            publicationCard.find('.publication-doi').text(publication.pub_doi || 'N/A');
                            $('#publicationsSection').append(publicationCard);
                        });
                        $('#publicationsCollapse').closest('.card').show();
                    } else {
                        $('#publicationsCollapse').closest('.card').hide();
                    }
    
                    // Handle Researchers Section
                    if (response.data.researchers && response.data.researchers.length > 0) {
                        $('#researchersSection').empty();
                        response.data.researchers.forEach(function (researcher, index) {
                            var researcherCard = $('#researcherTemplate').clone().removeAttr('id').show();
                            researcherCard.find('.researcher-number').text(index + 1);
                            researcherCard.find('.researcher-name').text((researcher.p_firstname + ' ' + researcher.p_surname) || 'N/A');
                            researcherCard.find('.researcher-title').text(researcher.p_title || 'N/A');
                            researcherCard.find('.researcher-email').text(researcher.p_email || 'N/A');
                            researcherCard.find('.researcher-affiliations').text(researcher.p_affiliations || 'N/A');
                            $('#researchersSection').append(researcherCard);
                        });
                        $('#researchersCollapse').closest('.card').show();
                    } else {
                        $('#researchersCollapse').closest('.card').hide();
                    }
    
                    // Handle Conditions Section
                    if (response.data.conditions) {
                        $('#allowedCountries').text(response.data.conditions.c_countries || 'N/A');
                        $('#profitUse').text(response.data.conditions.c_profituse || 'N/A');
                        $('#broadResearchUse').text(response.data.conditions.c_broadresearchuse || 'N/A');
                        $('#specificResearchUse').text(response.data.conditions.c_specificresearchuse || 'N/A');
                        $('#recontact').text(response.data.conditions.c_reconenct || 'N/A');
                        $('#conditionsCollapse').closest('.card').show();
                    } else {
                        $('#conditionsCollapse').closest('.card').hide();
                    }
    
    
                    // Show the modal
                    $('#viewDatasetModal').modal('show');
                    $('#viewDatasetModal').on('shown.bs.modal', function () {
                        // Expand all collapsible panels inside the modal
                        $(this).find('.collapse').each(function () {
                            var collapseElement = new bootstrap.Collapse(this, {
                                toggle: true // Ensure the collapse expands
                            });
                            collapseElement.show();
                        });
                    
                        console.log('All collapsible panels expanded.');
                    });
                } else {
                    swal("Error", "Failed to load dataset details.", "error");
                }
            },
            error: function () {
                swal("Error", "An error occurred. Please try again.", "error");
            }
        });
    });
    
    
    
    
    



    $(document).ready(function() {

        let dataType = [
            "Genetics",
            "Expression data", 
            "Epigenetics",
            "Biochemical data",
            "Phenotype",
            "Demographics",
            "Genomics",
            "Transcriptomics",
            "Epigenomics",
            "Microbiomics",
            "Metabolomics",
            "MRI", "CT" , "Ultrasound", "X-rays", "Mammography", "Bone density imaging", "Myelogram", "Arthrogram"
          ]
    
       $("#d_datatitle").select2({
            placeholder: "Write Title or Keyword and press Enter.",
            allowClear: false,
            theme: "bootstrap-5",
            width: '100%'
        });
    
        // d_datatype
    
        $("#d_datatype").select2({
            placeholder: "Please Select Data Types.",
            data: dataType,
            allowClear: false,
            theme: "bootstrap-5",
            width: '100%'
        });
    
        // d_datatheme
        $("#d_datatheme").select2({
            placeholder: "Please Select Data theme or department.",
            // data: dataType,
            allowClear: false,
            theme: "bootstrap-5",
            width: '100%'
        });
    
    })

    // Query Mechanism
    $(document).ready(function () {
        var table = $('#datasetTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": base_url + '/h',
                "type": "POST",
                "data": function (d) {
                    d.d_datatitle = $('#d_datatitle').val();  // Sends an array of selected values
                    d.d_datatype = $('#d_datatype').val();
                    d.d_datatheme = $('#d_datatheme').val();
                    d.d_studysize = $('#d_studysize').val();
                }
            },
            "columns": [
                { "data": 0 },
                { "data": 1 },
                { "data": 2 },
                { "data": 3 },
                { "data": 4 }
            ],
            "paging": true,
            "searching": true,
            "ordering": true
        });
    
        $('#build_query').on('click', function () {
            table.ajax.reload();
        });
    
        $('#reset_query').on('click', function () {
            $('#queryBuilder select').val(null).trigger('change');
            $('#d_studysize').val('');
            table.ajax.reload();
        });
    });
    

})