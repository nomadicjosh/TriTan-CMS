$(function () {
    $("#example1").DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "aaSorting": [],
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "deferRender": true
    });
    $('#example2').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": true,
        "autoWidth": false
    });
});