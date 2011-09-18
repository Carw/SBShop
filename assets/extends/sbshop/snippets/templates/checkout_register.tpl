<!--# register_form: Шаблон регистрационной формы #-->
<div class="right">
	<div>
		<h3>О доставке</h3>
		<p>Доставка <strong>по Екатеринбургу бесплатна</strong> до двери.</p>
		<p>Доставка по Свердловской области расчитывается отдельно.</p>
		<p>Смотрите также подробную <a href="[~26~]" target="_blank">информацию о доставке</a>.</p>
	</div>
</div>
<div class="content">
	<div class="regform">
		[+sb.error+]
		<form method="post" action="[+sb.link_action+]">
			<h3>[+lang.customer_fullname+]</h3>
			<p>
				<input type="text" name="sb_customer_fullname" value="[+sb.fullname+]" class="[+error_sb_customer_fullname+]" /> <span class="required">обязательное поле</span>
				<br />
				<span class="tips">Пример: Иванов Сергей Петрович</span>
			</p>
			<h3>[+lang.customer_phone+]</h3>
			<p>
				<input type="text" name="sb_customer_phone" value="[+sb.phone+]" class="[+error_sb_customer_phone+]" /> <span class="required">обязательное поле</span>
				<br />
				<span class="tips">Пример: 8-922-123-45-67</span>
			</p>
			<h3>[+lang.customer_email+]</h3>
			<p>
				<input type="text" name="sb_customer_email" value="[+sb.email+]" class="[+error_sb_customer_email+]" />
				<br />
				<span class="tips">Пример: ivanov.serg@mail.ru</span>
			</p>
			<h3>[+lang.customer_city+]</h3>
			<p>
				<input type="text" name="sb_customer_city" value="[+sb.city+]" class="[+error_sb_customer_city+]" /> <span class="required">обязательное поле</span>
				<br />
				<span class="tips">Пример: Екатеринбург</span>
			</p>
			<h3>[+lang.customer_address+]</h3>
			<p>
				<input type="text" name="sb_customer_address" value="[+sb.address+]" class="[+error_sb_customer_address+]" /> <span class="required">обязательное поле</span>
				<br />
				<span class="tips">Пример: ул. Ленина, д. 82, кв. 10</span>
			</p>
			<h3>[+lang.order_comment+]</h3>
			<p>
				<textarea name="sb_order_comment">[+sb.comment+]</textarea>
				<br />
				<span class="tips">Пример: 15 этаж, есть грузовой лифт.</span>
			</p>
			<p>
				<input class="button_active" type="submit" name="sb_customer_submit" value="[+lang.customer_submit+]" />
			</p>
		</form>
	</div>
</div>
<!--# error_outer: Шаблон вывода ошибок заполнения формы #-->
<div class="error">
	<p>Обратите внимание на правильность заполнения:</p>
	<ul>
		[+sb.wrapper+]
	</ul>
</div>
<!--# error_row: Шаблон конкретной ошибки #-->
<li>[+sb.row+]</li>