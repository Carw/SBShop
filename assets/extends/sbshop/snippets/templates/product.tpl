<!--# product: Основной шаблон товара #-->
<div class="productinfo">
	<h1>[+sb.longtitle+]</h1>
	<div class="photos">
		[+sb.thumbs+]
		<div class="bigphoto">
			<img src="[+sb.image.x480.1+]" alt="Купить [+sb.title+]" title="[+sb.title+]" class="bgimg">
		</div>
	</div>
</div>
<div class="right">
	<div>[+sb.description.3+]</div>
</div>
<div class="content">
	<h2>Характеристики</h2>
	[+sb.attributes+]
	[+sb.description.2+]
	<form id="product_[+sb.id+]" method="post" action="[+sb.link_action+]">
		<input type="hidden" name="sb_order_add" value="[+sb.id+]">
		<input type="hidden" name="sbprod[quantity]" value="1">
		<input type="hidden" name="baseprice" value="[+sb.price+]" />
		<div class="bundles" id="bundles">
			[+sb.base_bundle+]
			[+sb.bundles+]
		</div>
		[+sb.options+]
		<div class="summary">
			<div>
				Стоимость заказа: <span class="summ"><strong id="summ">[+sb.price+]</strong> руб</span>
			</div>
			<input class="button_active" type="submit" name="sb_order_submit" value="Добавить к заказу" />
		</div>
	</form>

	[+sb.description.1+]
</div>
<!--# absent_product: Отсутствующий товар #-->
<div class="productinfo">
	<h1>[+sb.longtitle+]</h1>
	<div class="photos">
		[+sb.thumbs+]
		<div class="bigphoto">
			<img src="[+sb.image.x480.1+]" alt="Купить [+sb.title+]" title="[+sb.title+]" class="bgimg">
		</div>
	</div>
</div>
<div class="right">
	<div>[+sb.description.3+]</div>
</div>
<div class="content">
	<div class="existence">[+sb.existence+]</div>
	<h2>Характеристики</h2>
	[+sb.attributes+]
	[+sb.description.2+]
	<form id="product_[+sb.id+]" method="post" action="[+sb.link_action+]">
		<input type="hidden" name="sb_order_add" value="[+sb.id+]">
		<input type="hidden" name="sbprod[quantity]" value="1">
		<input type="hidden" name="baseprice" value="[+sb.price+]" />
		[+sb.base_bundle+]
		[+sb.bundles+]
		[+sb.options+]
		<div class="summary">
			<div>
				Стоимость заказа: <span class="summ"><strong id="summ">[+sb.price+]</strong> руб</span>
			</div>
			<input class="button_active" type="submit" name="sb_order_submit" value="Добавить к заказу" />
		</div>
	</form>

	[+sb.description.1+]
</div>
<!--# thumbs_outer: Контейнер привьюшек фотографий #-->
<div class="thumbs">[+sb.wrapper+]</div>
<!--# thumbs_row: Шаблон пункта для представления привьюшек #-->
<div><img src="[+sb.image+]" class="thmb"></div>
<!--# attribute_outer: Контейнер для вывода характеристик товара #-->
<ul class="attributes">
	<li><span class="attr">Производитель</span> <span class="val">[+sb.vendor+]</span></li>
	[+sb.wrapper+]
</ul>
<!--# attribute_row: Шаблон пункта характеристики товара #-->
<li><span class="attr">[+sb.title+]</span> <span class="val">[+sb.value+] [+sb.measure+]</span></li>
<!--# options_outer: Общий контейнер для вывода списка опций #-->
<h2>Возможные опции</h2>
<p>Если название какой-то опции вам не известно, то вы можете найти его в <a href="[~38~]">кратком описании опций</a>.</p>
[+sb.wrapper+]
<!--# single_option_outer: Контейнер для вывода конкретной опции #-->
<div class="option [+sb.option.class+]">
	[+sb.wrapper+]
	<script type="text/javascript">
		notbundle['[+sb.option.id+]'] = '[+sb.option.notbundle+]';
	</script>
</div>
<!--# single_option_row: Шаблон для вывода опции где представлено всего одно значение #-->
	<input type="hidden" id="sboption_[+sb.option.id+]_[+sb.id+]_val" name="[+sb.id+]_sbprod[sboptions][[+sb.option.id+]]" value="[+sb.value+]" />
	<label>
		<div class="action">
			<input type="checkbox" id="sboption_[+sb.option.id+]_[+sb.id+]" class="optval" name="sbprod[sboptions][[+sb.option.id+]]" value="[+sb.id+]">
		</div>
		<div class="price">
			[+sb.price+]
		</div>
		<div class="option_title">
			[+sb.option.tip+][+sb.option.title+] [+sb.title+]
		</div>
	</label>
<!--# multi_option_outer: Контейнер для вывода конкретной опции #-->
<div class="option [+sb.option.class+]">
	<div class="option_title">[+sb.option.tip+][+sb.option.title+]</div>
	<div class="option_values">
		<ul>
			[+sb.wrapper+]
		</ul>
	</div>
	<script type="text/javascript">
		notbundle['[+sb.option.id+]'] = '[+sb.option.notbundle+]';
	</script>
</div>
<!--# multi_option_row: Шаблон для вывода опции где представлено несколько значений #-->
<li>
	<input type="hidden" id="sboption_[+sb.option.id+]_[+sb.id+]_val" name="[+sb.id+]_sbprod[sboptions][[+sb.option.id+]]" value="[+sb.value+]" />
	<label>
		<div class="action">
			<input type="radio" id="sboption_[+sb.option.id+]_[+sb.id+]" class="optval" name="sbprod[sboptions][[+sb.option.id+]]" value="[+sb.id+]">
		</div>
		<span class="option_price">[+sb.price+]</span>
		[+sb.title+]
	</label>
</li>
<!--# single_bundle_base: Вывод базовой комплектации если других комплектаций нет #-->
<div class="base_bundle">
	<div class="price">[+sb.price+] руб</div>
	<h2>Базовая комплектация</h2>
	[+sb.bundle.options+]
</div>
<!--# multi_bundle_base: Вывод базовой комплектации с другими комплектациями #-->
<div class="base_bundle">
	<input type="hidden" id="bundle_price_base" name="bundl_price_base" value="[+sb.price+]">
	<div class="action"><input type="radio" id="bundle_base" name="sbprod[bundle]" value="base" checked="checked"></div>
	<div class="price">[+sb.price+] руб</div>
	<h2>Базовая комплектация</h2>
	[+sb.bundle.options+]
	<script type="text/javascript">
		bundloptions['base'] = $.evalJSON('[]');
	</script>
</div>

<!--# bundle_outer: Контейнер для вывода списка комплектаций #-->
<h2>Дополнительные комплектации</h2>
[+sb.wrapper+]
<!--# bundle_row: Шаблон для вывода конкретной комплектации #-->
<div class="row">
	<label>
		<div class="action"><input type="radio" id="bundle_[+sb.bundle.id+]" name="sbprod[bundle]" value="[+sb.bundle.id+]" [+sb.bundle.checked+] /></div>
		<input type="hidden" id="bundle_price_[+sb.bundle.id+]" name="bundl_price_[+sb.bundle.id+]" value="[+sb.bundle.price+]">
		<div class="price">
			[+sb.bundle.price+] руб
		</div>
	</label>
	<div class="descr">
		<h3>[+sb.bundle.title+]</h3>
		[+sb.bundle.description+]
		[+sb.bundle.options+]
	</div>
	<script type="text/javascript">
		bundloptions['[+sb.bundle.id+]'] = $.evalJSON('[+sb.bundle.options.js+]');
	</script>
</div>
<!--# bundle_option_outer: Контейнер для вывода списка опций, связанных с комплектацией #-->
<ul class="options">
	[+sb.wrapper+]
</ul>
<!--# bundle_option_row: Шаблон для вывода конкретной опции, связанной с комплектацией #-->
<li>[+sb.title+][+sb.separator+] [+sb.value+]</li>
<!--# option_tip: Шаблон вывода подсказки к опции #-->
<div class="tips" rel="[+sb.id+]"></div>