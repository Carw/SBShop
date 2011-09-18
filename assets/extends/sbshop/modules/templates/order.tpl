<!--# orderinfo: Общий шаблон заказа #-->
<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">Электронный магазин</a>
		&raquo;
		<a href="[+module.link+]&mode=order">[+lang.order_list_title+]</a>
		&raquo;
		<span>[+lang.order_list_title+]</span>
	</div>
</div>
<div class="sectionBody">
	<h1>Заказ № [+sb.order.id+]</h1>
	<h2>Заказчик</h2>
	<p>ФИО: [+sb.customer.fullname+]</p>
	<p>Телефон: [+sb.customer.phone+]</p>
	<p>Адрес: [+sb.customer.city+], [+sb.customer.address+]</p>
	[+sb.comments+]
	<h2>Информация о товарах</h2>
	[+sb.products+]
	<h2>Полная стоимость заказа</h2>
	[+sb.order.price+]

	[+sb.action+]
</div>
<!--# action_outer: Контейнер для статуса заказа #-->
<h2>Действие с заказом</h2>
<form method="post" action="">
	<h3>Статус</h3>
	<select name="sb_status_list">[+sb.wrapper+]</select>
	<h3>Добавить комментарий</h3>
	<textarea name="sb_comment"></textarea>
	<br>
	<input type="submit" name="sb_set_status" value="Сохранить" />
</form>
<!--# action_option: Шаблон пункта статуса заказа #-->
<option value="[+sb.value+]">[+sb.title+]</option>
<!--# action_option_selected: Активный статус заказа #-->
<option value="[+sb.value+]" selected="selected">[+sb.title+]</option>
<!--# product_outer: Контейнер списка заказанных товаров #-->
<div class="products">
	[+sb.wrapper+]
</div>
<!--# product_row: Шаблон конкретного товара #-->
<div class="prod">
	<p>
		<span class="title"><a href="[+sb.url+]">[+sb.title+]</a> [+sb.bundle.title+]</span>
		-
		<span class="price">
			[+sb.price+] руб (x[+sb.quantity+])
		</span>
	</p>
	[+sb.options+]
</div>
<!--# product_option_outer: Контейнер списка опций для заказанного товара #-->
<ul class="optlist">[+sb.wrapper+]</ul>
<!--# product_option_row: Шаблон конкретной опции заказанного товара #-->
<li>[+sb.title+][+sb.separator+] [+sb.value+]</li>
<!--# comment_outer: Контейнер для вывода комментариев #-->
<h2>Комментарии:</h2>
<ul>
[+sb.wrapper+]
</ul>
<!--# comment_row: Шаблон комментария #-->
<li><span class="date">[+sb.time+]</span><br> [+sb.comment+]</li>