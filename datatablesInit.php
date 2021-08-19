<script>
$(document).ready(function() {
    $('#jobDataTables').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
	"ordering": true,
        "order": [],
        "info": true,
        "lengthMenu": [50, 100, 500],
        "autoWidth": false,
        "stateSave": true,
	"language":
	<?PHP
	    $languages = getAnglicizedLanguages();
	    echo file_get_contents('plugins/datatables-plugins/i18n/'.$languages[$language].'.lang');
	?>
    });
});
</script>
