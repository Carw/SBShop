<!--# order_outer: Контейнер списка заказов #-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-1.3.2.min.js\"></scr" + "ipt>");
	}
</script>
<script type="text/javascript" src="[+site.url+]assets/extends/sbshop/modules/templates/js/orderlist.js"></script>

<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">Электронный магазин</a>
		&raquo;
		<span>[+lang.order_list_title+]</span>
	</div>
</div>
<div class="sectionBody table">
	[+sb.wrapper+]
</div>
<!--# order_row: Шаблон для вывода конкретного заказа #-->
<form method="post" action="">

<table class="order status[+sb.status+]">
	<tr>
		<td class="header" colspan="2">
			<div class="opener"></div>
			Заказ № <span>[+sb.id+]</span> | Дата: <span>[+sb.date_edit+]</span> | <div class="status">[+sb.status.txt+]</div>
		</td>
	</tr>
	<tr class="outer">
		<td class="col">
			<h2>Данные заказчика</h2>
			<div class="content">
				<div class="contacts">
					<p>Заказчик: <span>[+sb.customer.fullname+]</span></p>
					<p>Телефон: <span>[+sb.customer.phone+]</span></p>
					<p>Email: <span>[+sb.customer.email+]</span></p>
					<p>Адрес: <span>[+sb.customer.city+], [+sb.customer.address+]</span></p>
				</div>
				<h3>Комментарии:</h3>
				<div class="comments">
					[+sb.comments+]
				</div>
			</div>
			<h2>Заметки</h2>
			<div class="content">
				<textarea id="add_comment" name="sb_comment[[+sb.id+]]"></textarea>
				<br>
				<input type="submit" class="right" name="sb_set_status" value="Сохранить комментарий" />
			</div>
		</td>
		<td class="col">
			<h2>Содержание заказа</h2>
			<div class="content">
				[+sb.products+]
				<p align="right">
					Общая сумма: <span class="total">[+sb.price+]</span> руб
				</p>
			</div>
			<h2>Параметры заказа</h2>
			<div class="content">
				<p>
					Состояние:
					<select name="sb_status_list[[+sb.id+]]">[+sb.action+]</select>
					<input type="submit" name="sb_set_status" value="Сохранить состояние" />
				</p>
			</div>
		</td>
	</tr>
</table>
</form>
<!--# comment_outer: Контейнер для вывода комментариев #-->
[+sb.wrapper+]
<!--# comment_row: Шаблон комментария #-->
<p><span class="date">[+sb.time+]</span> [+sb.comment+]</p>
<!--# product_outer: Контейнер списка заказанных товаров #-->
<div class="products">
	[+sb.wrapper+]
</div>
<!--# product_row: Шаблон конкретного товара #-->
<div class="prod">
	<span class="title">[+sb.title+] [+sb.bundle.title+]</span>
	<span class="price">
		<small>[+sb.quantity+] x</small> [+sb.price+] = <span class="summ">[+sb.summ+] руб</span>
	</span>
	<br>
	[+sb.options+]
</div>
<!--# product_option_outer: Контейнер списка опций для заказанного товара #-->
<span class="optlist">[+sb.wrapper+]</span>
<!--# product_option_row: Шаблон конкретной опции заказанного товара #-->
[+sb.title+][+sb.separator+] [+sb.value+]
<!--# product_option_separator: Шаблон конкретной опции заказанного товара #-->
,&nbsp;
<!--# action_outer: Контейнер для статуса заказа #-->
[+sb.wrapper+]
<!--# action_option: Шаблон пункта статуса заказа #-->
<option value="[+sb.value+]">[+sb.title+]</option>
<!--# action_option_selected: Активный статус заказа #-->
<option value="[+sb.value+]" selected="selected">[+sb.title+]</option>