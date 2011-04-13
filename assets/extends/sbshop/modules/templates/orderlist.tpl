<!--# order_outer: Контейнер списка заказов #-->
<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">Электронный магазин</a>
		&raquo;
		<span>[+lang.order_list_title+]</span>
	</div>
</div>
<div class="sectionBody">
	<ul>[+sb.wrapper+]</ul>
</div>
<!--# order_row: Шаблон для вывода конкретного заказа #-->
<li>
	<a href="[+sb.link+]">Заказ №[+sb.id+]</a> - [+sb.status+] ([+sb.date_edit+]) - [+sb.price+] руб
</li>