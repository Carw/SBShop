<!--# order_outer: Контейнер списка заказов #-->
<style type="text/css">
@import "[+site.url+]assets/libs/dyndatetime/css/calendar-blue.css";
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-1.7.2.min.js\"></scr" + "ipt>");
	}
</script>
<script type="text/javascript" src="[+site.url+]assets/libs/dyndatetime/jquery.dynDateTime.js"></script>
<script type="text/javascript" src="[+site.url+]assets/libs/dyndatetime/lang/calendar-ru-my.js"></script>
<script type="text/javascript" src="[+site.url+]assets/libs/javascript/jquery.autocomplete.js"></script>
<script type="text/javascript" src="[+site.url+]assets/libs/javascript/jquery.json-2.3.min.js"></script>
<script type="text/javascript" src="[+site.url+]assets/extends/sbshop/modules/templates/js/orderlist.js"></script>

<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">[+lang.sbshop+]</a>
		&raquo;
		<span>[+lang.order_list_title+]</span>
	</div>
</div>
<div class="sectionBody table">
	<div id="actionPanel">
		<form method="post" action="">
			<ul>
				<li>
					<label>
						Статус заказа:
						<select name="order_status">
							<option value="active" [+sb.status_active_selected+]>[+lang.order_list_active+]</option>
							<option value="10" [+sb.status_10_selected+]>[+lang.order_status_10+]</option>
							<option value="20" [+sb.status_20_selected+]>[+lang.order_status_20+]</option>
							<option value="25" [+sb.status_25_selected+]>[+lang.order_status_25+]</option>
							<option value="30" [+sb.status_30_selected+]>[+lang.order_status_30+]</option>
							<option value="0" [+sb.status_0_selected+]>[+lang.order_status_0+]</option>
							<option value="-10" [+sb.status_-10_selected+]>[+lang.order_status_-10+]</option>
							<option value="-20" [+sb.status_-20_selected+]>[+lang.order_status_-20+]</option>
							<option value="-30" [+sb.status_-30_selected+]>[+lang.order_status_-30+]</option>
						</select>
					</label>
				</li>
				<li>
					<input type="submit" value="Вывести">
				</li>
			</ul>
		</form>
	</div>
	<table class="orderlist">
		[+sb.wrapper+]
	</table>
</div>
<div id="popup">
	<a href="#" class="close btn">X</a>
	<div class="h"></div>
	<div class="info"></div>
</div>
<div id="opacity"></div>
<script type="text/javascript">
	var OrderData = $.parseJSON('[+sb.jsdata+]');
	var ProdData = $.parseJSON('[+sb.jsdatanew+]');
</script>
<!--# order_row: Шаблон для вывода конкретного заказа #-->
<tr class="order status[+sb.status+]">
	<td>
		<form method="post" action="">
			<table class="orderinfo">
				<tr>
					<td class="header" colspan="2">
						<div class="opener"></div>
						Заказ № <span>[+sb.id+]</span> | Дата: <span>[+sb.date_edit+]</span> | Контакт: <span class="date_next">[+sb.date_next+]</span> | <div class="status">[+sb.status.txt+]</div>
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
						<div class="content order-[+sb.id+]">
							[+sb.products+]
							<p align="right">
								Общая сумма: <span class="total">[+sb.price.format+] [+sb.currency+]</span>
							</p>
							<p>
								<input class="order_edit" data="[+sb.id+]" type="button" name="sb_edit" value="Редактировать заказ">
							</p>
						</div>
						<h2>Параметры заказа</h2>
						<div class="content">
							<p>
								Состояние:<br>
								<select name="sb_status_list[[+sb.id+]]">[+sb.action+]</select>
								<br>
								<input type="submit" name="sb_set_status" value="Сохранить состояние" />
							</p>
							<p>
								Дата следующего контакта:<br>
							</p>
							<input type="hidden" name="sb_date_next[[+sb.id+]]" class="sb_date_next" value="" />
							<div class="sb_date_next_calend"></div>
							<p>
								<input type="submit" name="sb_set_status" value="Установить дату" />
							</p>
						</div>
					</td>
				</tr>
			</table>
		</form>
	</td>
</tr>
<!--# order_row1: Шаблон для вывода конкретного заказа #-->
<form method="post" action="">

<table class="order status[+sb.status+]">

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
	<input type="hidden" class="setid" value="[+sb.set_id+]" />
	<span class="title"><a href="/magazin[+sb.url+]" target="_blank">[+sb.title+]</a></span>
	<span class="price">
		<small>[+sb.quantity+] x</small> [+sb.price.format+] = <span class="summ">[+sb.summ.format+] [+sb.currency+]</span>
	</span>
	[+sb.sku+]
	<br>
	[+sb.options+]
</div>
<!--# product_option_outer: Контейнер списка опций для заказанного товара #-->
<span class="optlist">[+sb.wrapper+]</span>
<!--# product_option_row: Шаблон конкретной опции заказанного товара #-->
[+sb.title+]
<!--# product_option_separator: Шаблон конкретной опции заказанного товара #-->
,&nbsp;
<!--# action_outer: Контейнер для статуса заказа #-->
[+sb.wrapper+]
<!--# action_option: Шаблон пункта статуса заказа #-->
<option value="[+sb.value+]">[+sb.title+]</option>
<!--# action_option_selected: Активный статус заказа #-->
<option value="[+sb.value+]" selected="selected">[+sb.title+]</option>