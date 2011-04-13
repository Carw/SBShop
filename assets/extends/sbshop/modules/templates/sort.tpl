<!--# sort_outer: Шаблон сервиса для сортировки #-->
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