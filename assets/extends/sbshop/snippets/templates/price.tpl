<!--# outer: Контейнер раздела #-->
<div class="pricelist">
	<div class="search">
		<input type="text" id="price_search" name="price_search" value="" />
		<table>
			[+sb.wrapper+]
		</table>
	</div>
</div>
<p>Разделов: <b>[+sb.cnt.categories+]</b></p>
<p>Товаров: <b>[+sb.cnt.products+]</b></p>
<p>Комплектаций: <b>[+sb.cnt.bundles+]</b></p>
<p>Опций: <b>[+sb.cnt.options+]</b></p>
<p>Всего записей в прайсе: <b>[+sb.cnt.all+]</b></p>
<!--# category_row: Пункт раздела #-->
<tr class="category">
	<td colspan="2"><a href="[+sb.url+]">[+sb.title+]</a></td>
</tr>
[+sb.wrapper+]
<!--# product_row: Пункт товара #-->
<tr class="product">
	<td class="image"><img src="[+sb.image.0+]"></td>
	<td>
		<table>
			<tr class="base">
				<td class="title"><a href="[+sb.url+]">[+sb.longtitle+]</a></td>
				<td class="cost">[+sb.price.format+]</td>
				<td class="checkbox">
					<input type="checkbox" class="price_check" value="[+sb.price+]">
				</td>
			</tr>
			[+sb.bundles+]
			[+sb.options+]
		</table>
	</td>
</tr>
<!--# bundle_row: Пункт товара #-->
<tr class="bundle">
	<td class="title">[+sb.title+]</td>
	<td class="cost">[+sb.price.format+]</td>
	<td class="checkbox">
		<input type="checkbox" class="price_check" value="[+sb.price+]">
	</td>
</tr>
<!--# option_row: Пункт товара #-->
<tr class="option">
	<td class="title">[+sb.title+]</td>
	<td class="cost">[+sb.value+]</td>
	<td class="checkbox">
		<input type="checkbox" class="price_check" value="[+sb.value+]">
	</td>
</tr>