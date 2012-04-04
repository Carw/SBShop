<!--# pricing_outer: Шаблон сервиса для сортировки #-->
<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">[+lang.sbshop+]</a>
		&raquo;
		<span>Управление ценами</span>
	</div>
</div>
<div class="sectionBody">
	<form method="post" action="">
		<h3>Условие фильтра</h3>
		<br>
		<label>
			Раздел:
			<select name="pricing_category">
				<option value="all" selected="selected">Все разделы</option>
				[+sb.categories+]
			</select>
		</label>
		<br>
		<br>
		<label>
			Производитель:
			<select name="pricing_vendor">
				<option value="all" selected="selected">Все производители</option>
				[+sb.vendors+]
			</select>
		</label>
		<br>
		<br>
		<div>
			<label for="pricing_ids">Список товаров:</label>
		</div>
		<input type="text" id="pricing_ids" name="pricing_ids" style="width: 80%;" />
		<br><br>
		<label for="pricing_add">Надбавка</label>
		<br>
		<input type="text" id="pricing_add" name="pricing_add" style="width: 80%;" />
        <p>Можно использовать следующие форматы:</p>
        <p><strong>IDтовара</strong> - установить надбавку для товара.</p>
        <p><strong>IDтовара.IDопции:IDзначения</strong> - установить надбавку для значения конкретной опции у конкретного товара.</p>
        <p><strong>IDтовара.IDопции</strong> - установить надбавку для всех значений (которые содержат число) конкретной опции.</p>
        <p><strong>IDопции:IDзначения</strong> - установить надбавку для значений конкретной опции для всех товаров в списке.</p>
        <br>
		<input type="submit" id="button_pricing" name="sb_submit_pricing" value="Сохранить" />
	</form>
</div>
<!--# pricing_category_row: Шаблон пункта раздела #-->
<option value="[+sb.id+]">[+sb.title+]</option>
<!--# pricing_vendor_row: Шаблон пункта раздела #-->
<option value="[+sb.title+]">[+sb.title+]</option>