<!--# sort_outer: Шаблон сервиса для сортировки #-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-1.3.2.min.js\"></scr" + "ipt>" + "<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-ui-1.8.4.custom.min.js\"></scr" + "ipt>");
	}
</script>
<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">Электронный магазин</a>
		&raquo;
		<span>Сортировка</span>
	</div>
</div>
<div class="sectionBody">
	<form method="post" action="">
		<ul id="sortable">
			[+sb.wrapper+]
		</ul>
		<p><input type="submit" id="letsort" name="sb_sort" value="Сохранить" /></p>
	</form>
</div>
<script>
$(function() {
	$( "#sortable" ).sortable({
		placeholder: "ui-state-highlight",
		axis: 'y',
		cursor: 'move'
	});
	$( "#sortable" ).disableSelection();
});

$("#letsort").button();

</script>
<!--# sort_row: Строка сортировки #-->
<li class="ui-state-default" rel="[+sb.id+]">
	<input type="hidden" name="sort[]" value="[+sb.id+]" />
	[+sb.title+] ([+sb.id+])
</li>