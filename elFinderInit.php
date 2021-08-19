<script src='js/elfinder.min.js'></script>
<script src='js/i18n/elfinder.<?PHP echo $_SESSION['language'];?>.js'></script>

<!-- init -->
<script>
$(function() {
    $('#elfinder').elfinder({
	url: 'php/connector.minimal.php?path=<?PHP echo $olpath;?>',
	lang: '<?PHP echo $_SESSION['language'];?>'
    });
});
</script>
