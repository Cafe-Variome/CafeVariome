$(document).ready(function() {
    $("#selectNetwork").select2({placeholder: "--Select a network--"});
});

function networkSelect(){
    if($("#selectNetwork").val()) 
        window.location = baseurl + "discover/query_builder/" + $("#selectNetwork").val();
    else 
        alert("Select a network to search");
    
}