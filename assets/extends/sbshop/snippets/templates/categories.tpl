<!--# category_outer: Контейнер для вывода разделов #-->
[+sb.wrapper+]
<!--# category_group: Шаблон для группировки товаров. С его помощью можно организовать нормальную таблицу #-->
<div class="group">[+sb.wrapper+]</div>
<!--# category_row: Шаблон конкретного раздела #-->
<div class="products">
	<h2><a href="[+sb.url+]">[+sb.title+]</a></h2>
	[+sb.wrapper+]
	[+sb.products+]
</div>

<p><a href="[+sb.url+]">Все [+sb.title.l+]</a></p>
<!--# category_inner: Контейнер для вывода вложенных разделов #-->
<ul class="sbsubcat [+sb.class+]">[+sb.wrapper+]</ul>
<!--# category_innerrow: Шаблон конкретного вложенного подраздела #-->
<li><a href="[+sb.url+]">[+sb.title+]</a>[+sb.wrapper+]</li>