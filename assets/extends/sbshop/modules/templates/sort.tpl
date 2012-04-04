<!--# sort_outer: Шаблон сервиса для сортировки #-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"/assets/libs/javascript/jquery-1.3.2.min.js\"></scr" + "ipt>");
	}
</script>
<script type="text/javascript" src="/assets/libs/javascript/jquery.dragsort-0.4.3.min.js"></script>
<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">[+lang.sbshop+]</a>
		&raquo;
		<span>Сортировка</span>
	</div>
</div>
<div class="sectionBody">
	<form method="post" action="">
		<table class="sorttable">
			[+sb.wrapper+]
		</table>
		<p><input type="submit" id="letsort" name="sb_sort" value="Сохранить" /></p>
	</form>
</div>
<script>
$(function() {
	$('.sorttable tbody').dragsort({
		itemSelector: 'tr',
		dragSelector: "td.dragHandle",
		placeHolderTemplate: '<tr class="option"><td class="dragHandle"><div>&nbsp;</div></td><td></td></tr>'
	});
});
</script>
<!--# sort_row: Строка сортировки #-->
<tr>
	<td class="dragHandle"><div>&nbsp;</div></td>
	<td>
		<table class="inner">
			<tr class="option_header">
				<td class="header">
					<input type="hidden" name="sort[]" value="[+sb.id+]" />
					[+sb.title+] ([+sb.id+])
				</td>
			</tr>
		</table>
	</td>
</tr>