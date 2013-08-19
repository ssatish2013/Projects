$(document).ready(function(){ 
    $("img.loader").hide();
    $("div#accordion > *").show();
    $("#accordion").accordion({ 
        autoHeight: false,
        collapsible: true,
    });
 });
