/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$chromosome                 = ["chr1","chr2","chr3","chr4","chr5","chr6","chr7","chr8","chr9","chr10","chr11","chr12","chr13","chr14","chr15","chr16","chr17","chr18","chr19","chr20","chr21","chr22","chrX","chrY"];
$accession                  = ["NG_007124.1","NG_007146.1","NG_007147.2"];
//$accession                  = ["NG_007124.1","NG_007146.1","NG_007147.2","NG_007148.2","NG_007149.1","NG_007150.1","NG_007151.1","NG_007159.2","NG_007161.1","NG_007259.1","NG_007260.1","NG_007261.1","NG_007262.1","NG_007263.1","NG_007264.1","NG_007265.1","NG_007266.1","NG_007267.1","NG_007268.1","NG_007269.1","NG_007270.2","NG_007271.1","NG_007272.1","NG_007273.1","NG_007274.1","NG_007275.1","NG_007276.1","NG_007277.1","NG_007278.1","NM_001003697.1","NM_001003698.3","NM_001003699.3","NM_001003700.1","NM_001003701.1","NM_001003702.2","NM_001003703.1","NM_001003704.2","NM_001003712.1","NM_001003713.2","NM_001003714.2","NM_001003715.3","NM_001003716.3","NM_001003722.1","NM_001003745.1","NM_001003750.1","NM_001001417.5","NM_001001418.4","NM_001001419.1","NM_001001420.1","NM_001001430.1","NM_001001431.1","NM_001001432.1","NM_001001433.2","NM_001001435.2","NM_001001436.2","NM_001001437.3","NM_001001438.2","NM_001001479.2","NM_001001480.2","NM_001001481.2","NM_001001483.2","NM_001001484.2","NM_001001485.2","NM_001001486.1","NM_001001487.1","NM_001001502.1","NM_001001503.1","NM_001001520.1","NM_001001521.1","NM_001001522.1","NM_001001523.1","NM_001001524.2","NM_001001547.2","NM_001001548.2","NM_001001549.2","NM_001001550.2","NM_001001551.3","NM_001001552.4","NM_001001555.2","NM_001001556.1","NM_001001557.2","NM_001001560.2","NM_001001561.2","NM_001001563.1","NM_001001567.1","NM_001001568.1","NM_001001569.1","NM_001001570.1","NM_001001571.1","NM_001001572.1","NM_001001573.1","NM_001001574.1","NM_001001575.1","NM_001001576.1","NM_001001577.1","NM_001001578.1","NM_001001579.1","NM_001001580.1","NM_001001581.1","NM_001001582.1","NM_001001583.1","NM_001001584.2","NM_001001585.1","NM_001001655.2","NM_001001656.1","NM_001001657.1","NM_001001658.1","NM_001001659.1","NM_001001660.2","NM_001001661.2","NM_001001662.1","NM_001001663.1","NM_001001664.2","NM_001001665.3","NM_001001666.3","NM_001001667.1","NM_001001668.3","NM_001001669.2","NM_001001670.2","NM_001001671.3","NM_001001673.3","NM_001001674.1","NM_001001676.1","NM_001001683.2","NM_001001694.2","NM_001001701.3","NM_001001709.2"];
$build                      = ["GRCh38","hg38","GRCh37","hg19","hg18","NCBI Build 36.1"];


var link = "";
$(document).on('click', '.show_dialog', function(e) {
	e.preventDefault();

	link = $(this).attr('href');

	$("#total_derid_count").html($(this).parent().prev().html().trim());
	$("#derids_count").val('');
	$("input[type=radio][name=optradio]").prop('checked', false);
	$("#chooseDerid").modal({
		backdrop: 'static',
  		keyboard: false
	});
	$("#chooseDerid").modal('show');
});

$(document).on('click', '#show_derids2', function(e) {
	e.preventDefault();

	$("#derid_error").html("");
	$("#derid_error2").html("");
	
	var count = $("#derids_count").val().trim();
	if(isNaN(parseFloat(count)) || !isFinite(count)) {
		$("#derid_error").html("Error: Value is either null or not numberic");
		return;
	}

	count = parseInt(count);
	totalcount = parseInt($("#total_derid_count").html());

	if(count > totalcount) {
		$("#derid_error").html("Error: Value cannot be greater than the total count.");
		return;
	} else if(count < 0) {
		$("#derid_error").html("Error: Value cannot be less than zero.");
		return;
	}

	

	view_type = $("input[name=optradio]:checked").val();

	if(view_type == undefined) {
		$("#derid_error2").html("Error: Select any one of the above choice.");
		return;
	}

	// $("#chooseDerid").modal('hide');

	console.log(view_type);
	window.open(link + "/" + count + "/" + view_type);
});























