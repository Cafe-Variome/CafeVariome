function select_groups() {
    $(".groupsSelected").find('option').each(function () {
        $(this).attr('selected', 'selected');
    });
}