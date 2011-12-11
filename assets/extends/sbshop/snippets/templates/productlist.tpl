<!--# product_list: Контейнер для списка продуктов текущего раздела #-->
<div class="products">
	[+sb.wrapper+]
</div>
<!--# products_absent: Товаров нет #-->
<div class="products">
	<p>К сожалению, подходящих товаров нет.</p>
</div>
<!--# products_outer: Контейнер для списка продуктов #-->
<div class="products">
	[+sb.wrapper+]
</div>
<!--# product_row: Шаблон активного товара в списке #-->
<div class="prod">
	<a href="[+sb.url+]"><img src="[+sb.image.x228.1+]" alt="[+sb.title+]. Уменьшенная фотография." /></a>
	<h3>[+sb.title+]</h3>
	<p class="vendor">[+sb.vendor+]</p>
	[+sb.attributes+]
	<p class="link"><a href="[+sb.url+]">Подробная информация</a></p>
	<p class="price">[+sb.price_full+] руб</p>
</div>
<!--# product_absent_row: Шаблон неактивного товара в списке #-->
<div class="prod absent">
	<a href="[+sb.url+]"><img src="[+sb.image.x228.1+]" alt="[+sb.title+]. Уменьшенная фотография." /></a>
	<h3>[+sb.title+]</h3>
	<p class="vendor">[+sb.vendor+]</p>
	[+sb.attributes+]
	<p class="link"><a href="[+sb.url+]">Подробная информация</a></p>
	<p class="price">[+sb.price_full+] руб <span class="existence">[+sb.existence+]</span></p>
</div>
<!--# attribute_outer: Контейнер для вывода характеристик товара #-->
<ul>[+sb.wrapper+]</ul>
<!--# attribute_row: Шаблон характеристики #-->
<li>[+sb.title+]: [+sb.value+] [+sb.measure+]</li>