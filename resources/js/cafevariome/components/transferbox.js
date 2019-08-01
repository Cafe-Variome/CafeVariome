/**
 * transferbox.js
 * Created 30/07/2019
 * 
 * @author Dhiwagaran Thangavelu
 * @author Mehdi Mehtarizadeh
 * 
 * This file contains JavaScript code for transferring items between two lists.
 */

function moveItem(event){
    $nextTag = $(event.currentTarget).parent().next().find("select")[0];
    $prevTag = $(event.currentTarget).parent().prev().find(":selected");
    $prevTag.each(function () {
        $nextTag.append($("<option></option>")
                .attr("value", $(this).val())
                .text($(this).html())[0]);
        $(this).remove();
    });
}

function removeItem(event){
    $prevTag = $(event.currentTarget).parent().prev().find("select")[0];
    $nextTag = $(event.currentTarget).parent().next().find(":selected");
    $nextTag.each(function () {
        $prevTag.append($("<option></option>")
                .attr("value", $(this).val())
                .text($(this).html())[0]);
        $(this).remove();
    });
}
