/**
 * datatable.js
 * Created: 20/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 */

 function datatableInit(tableID){
    if ($('#'+tableID).length) {
        $('#'+tableID).DataTable();
    }
 }