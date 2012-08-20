<!--# category_form: Форма редактирования раздела #-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-1.3.2.min.js\"></scr" + "ipt>");
	}
</script>
<script type="text/javascript" src="[+site.url+]assets/libs/javascript/jquery.tablednd_0_5.js"></script>
<script type="text/javascript" src="[+site.url+]assets/extends/sbshop/modules/templates/js/category.js"></script>

<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">[+lang.sbshop+]</a>
		&raquo;
		<span>[+lang.category_edit+]</span>
	</div>
</div>
<div class="sectionBody">

	<h1>[+lang.category_edit+]</h1>

	<form name="mutate" id="mutate" class="content" method="post" enctype="multipart/form-data" action="[+module.link+]&mode=cat&act=[+module.act+]">
	<input type="hidden" name="catid" value="[+category.id+]" />
	<input type="hidden" name="parid" value="[+category.parent+]" />
	<input type="hidden" name="ok" value="true" />

	<div id="actions">
		<ul class="actionButtons">
			<li id="Button1">
				<a href="#" onclick="document.mutate.submit();">
					<img src="[+style.icons_save+]" />[+lang.save+]
				</a>
			</li>
			<li id="Button4"><a href="#" onclick="document.location.href='[+module.link+]';"><img src="[+style.icons_cancel+]" />[+lang.cancel+]</a></li>
		</ul>
	</div>

	[+category.error+]

	<div class="sectionBody">
		<div class="tab-pane" id="docManagerPane">
			<script type="text/javascript">
				tpResources = new WebFXTabPane(document.getElementById("docManagerPane"));
			</script>

			<div class="tab-page" id="tabGeneral">
				<h2 class="tab">[+lang.category_tab_general+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabGeneral"));</script>
				<table width="99%" border="0" cellspacing="5" cellpadding="0">
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.category_title+]</span></td>
						<td><input name="title" type="text" maxlength="255" value="[+category.title+]" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.category_title_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.category_longtitle+]</span></td>
						<td><input name="longtitle" type="text" maxlength="255" value="[+category.longtitle+]" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.category_longtitle_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.category_alias+]</span></td>
						<td><input name="alias" type="text" maxlength="255" value="[+category.alias+]" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.category_alias_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.category_favorite+]</span></td>
						<td><input name="favorite" type="text" maxlength="255" value="[+category.favorite+]" class="inputBox" onchange="documentDirty=true;" spellcheck="false" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.category_favorite_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.category_published+]</span></td>
						<td><input name="published" type="checkbox" maxlength="255" value="1" class="inputBox" onchange="documentDirty=true;" spellcheck="true" [+category.published+] />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.category_published_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
				</table>

				<div class="sectionHeader">[+lang.category_content+]</div>
				<div class="sectionBody">
					<div style="width:100%">
						<textarea id="ta" name="description" style="width:100%; height: 400px;" onchange="documentDirty=true;">[+category.description+]</textarea>
					</div>
				</div>
			</div>
			<div class="tab-page" id="tabAttributes">
				<h2 class="tab">[+lang.category_tab_attributes+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabAttributes"));</script>
				<div id="attributes" style="width:100%">
					<div class="attribute_group opened">
						<div class="header">
							<div class="opener"></div>
							<div class="attribute_group_name">Общая группа</div>
						</div>
						<div class="attribute_group_outer">
							<table class="sorttable">
								[+sb.attributes+]
							</table>
						</div>
						<button class="new_attribute_add button_add">[+lang.product_attribute_add+]</button>
					</div>
				</div>
				<div class="templates">
					<table>
						<tr class="attribute_template">
							<td class="dragHandle"></td>
							<td>
								<div class="attribute">
									<div class="attributedel">
										<input type="image" class="attribute_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
									</div>
									<input type="text" class="attribute_title" name="attribute_title[]">
									<input type="text" class="attribute_measure" name="attribute_measure[]">
									<select name="attribute_type[]">
										<option value="n" selected="selected">[+lang.product_attribute_type_normal+]</option>
										<option value="p" style="color: green;">[+lang.product_attribute_type_primary+]</option>
										<option value="h" style="color: silver;">[+lang.product_attribute_type_hidden+]</option>
									</select>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="tab-page" id="tabOptions">
				<h2 class="tab">[+lang.product_tab_options+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabOptions"));</script>
				<div id="options" style="width:100%">
					<table class="option_template">
						<tr class="option">
							<td class="dragHandle"></td>
							<td>
								<table class="inner">
									<tr class="option_header">
										<td class="header">
											<h3 class="title"></h3>
										</td>
										<td class="actions">
											<div class="optiondel">
												<input type="image" class="option_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
											</div>
										</td>
									</tr>
									<tr class="content">
										<td colspan="2">
											<div>
												<input type="hidden" name="option_id[]" value="[+option.id+]">
												<p>
													<label>
														[+lang.product_options_title+]<br>
														<input type="text" class="option_name" name="option_name[]" value="">
													</label>
												</p>
												<div class="split"></div>
												<div>
													[+lang.product_options_value_tips+]<br>
												</div>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<table class="sorttable">
						[+options+]
					</table>
				</div>
				<div class="option_toolbar">
					<input type="text" id="new_option_name" value=""> <button class="new_attribute_add button_add" id="new_option_add">[+lang.product_option_add+]</button>
				</div>
				<script type="text/javascript">
					// Заголовок комплектации по умолчанию
					option_name = '[+lang.product_option_name+]';
				</script>
			</div>
			<div class="tab-page" id="tabFilters">
				<h2 class="tab">[+lang.category_tab_filter+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabFilters"));</script>
				<div id="filters" style="width:100%">
					<table class="sorttable">
						[+sb.filter+]
					</table>
				</div>
				<div class="templates">
					<div class="values value_template">
						<div class="col1">
							<input class="filter_value_title" style="width: 200px;" type="text" value="">
						</div>
						<div class="col3">
							<div class="rng">
								<input class="filter_value_min" type="text" style="width: 100px;" value="">
								&ndash;
								<input class="filter_value_max" type="text" style="width: 100px;" value="">
							</div>
							<div class="eqv">
								<input class="filter_value_eqv" type="text" style="width: 100px;" value="">
							</div>
						</div>
						<div class="del">
							<input type="image" style="width: auto;" class="filter_value_del" src="/manager/media/style/MODxCarbon/images/icons/delete.png" title="[+lang.category_filter_del_hint+]">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</form>
</div>
<!--# filter_outer #-->
<tr class="filter">
	<td style="width: 20px;"><input type="checkbox" class="on" name="filter_on[[+sb.id+]]" value="1" [+sb.on+]></td>
	<td>
		<table class="inner">
			<tr class="filter_header">
				<td class="header">
					<div>
						<h3 class="title">[+sb.title+]</h3>
					</div>
				</td>
			</tr>
			<tr class="content">
				<td>
					<div>
						<div>
							<select id="filter_type_[+sb.id+]" class="filter_type" name="filter_type[[+sb.id+]]">
								<option value="eqv" [+sb.eqv+]>[+lang.category_filter_type_evq+]</option>
								<option value="rng" [+sb.rng+]>[+lang.category_filter_type_rng+]</option>
								<option value="vrng" [+sb.vrng+]>[+lang.category_filter_type_vrng+]</option>
							</select>
						</div>
						<div class="split"></div>
						<div id="values_[+sb.id+]">
							<div class="th">
								<div class="col1">[+lang.category_filter_title+]</div>
								<div class="col3">[+lang.category_filter_value+]</div>
							</div>
							[+sb.values+]
						</div>
						<div>
							<button class="option_value_add button_add" value="[+sb.id+]">[+lang.product_option_value_add+]</button>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!--# filter_value #-->
<div class="values">
	<div class="col1">
		<input class="filter_value_title" style="width: 200px;" type="text" name="filter_value_title[[+sb.id+]][]" value="[+sb.value.title+]">
	</div>
	<div class="col3">
		<div class="rng [+sb.rng.visible+][+sb.vrng.visible+]">
			<input class="filter_value_min" type="text" style="width: 100px;" name="filter_value_min[[+sb.id+]][]" value="[+sb.value.min+]">
			&ndash;
			<input class="filter_value_max" type="text" style="width: 100px;" name="filter_value_max[[+sb.id+]][]" value="[+sb.value.max+]">
		</div>
		<div class="eqv [+sb.eqv.visible+]">
			<input class="filter_value_eqv" type="text" style="width: 100px;" name="filter_value_eqv[[+sb.id+]][]" value="[+sb.value.eqv+]">
		</div>
	</div>
	<div class="del">
		<input type="image" style="width: auto;" class="filter_value_del" src="/manager/media/style/MODxCarbon/images/icons/delete.png" title="[+lang.category_filter_del_hint+]">
	</div>
</div>
<!--# attribute_outer: Контейнер для параметров #-->
<tr>
	<td class="dragHandle"></td>
	<td>
		<div class="attribute">
			<input type="hidden" name="attribute_id[]" value="[+attribute.id+]">
			<input type="text" class="attribute_title" name="attribute_title[]" value="[+attribute.title+]">
			<input type="text" class="attribute_measure" name="attribute_measure[]" value="[+attribute.measure+]">
			<select name="attribute_type[]">
				<option value="n" [+attribute.type.normal+]>[+lang.product_attribute_type_normal+]</option>
				<option value="p" style="color: green" [+attribute.type.primary+]>[+lang.product_attribute_type_primary+]</option>
				<option value="h" style="color: silver;" [+attribute.type.hidden+]>[+lang.product_attribute_type_hidden+]</option>
			</select>
		</div>
	</td>
</tr>
<!--# option_outer: Контейнер для опций #-->
<tr class="option">
	<td class="dragHandle"><div>&nbsp;</div></td>
	<td>
		<table class="inner">
			<tr class="option_header">
				<td class="header">
					<div>
						<h3 class="title">[+option.title+] ([+option.id+])</h3>
					</div>
				</td>
				<td class="actions">
					<div class="optiondel">
						<input type="image" class="option_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
					</div>
				</td>
			</tr>
			<tr class="content">
				<td colspan="2">
					<div>
						<input type="hidden" name="option_id[[+option.key+]]" value="[+option.id+]">
						<p>
							<label>
								[+lang.product_options_title+]<br>
								<input type="text" class="option_name" name="option_name[[+option.key+]]" value="[+option.title+]">
							</label>
						</p>
						<div class="optiondel">
							<input type="image" style="width: auto;" class="option_extend" src="/manager/media/style/MODxCarbon/images/icons/table.gif">
						</div>
						<div class="extend visible">
							<p>
								<label>
									[+lang.product_options_class+]<br>
									<input type="text" class="option_class" name="option_class[[+option.key+]]" value="[+option.class+]">
								</label>
							</p>
							<p>
								<label>
									[+lang.product_options_image+]<br>
									<input type="text" class="option_image" name="option_image[[+option.key+]]" value="[+option.image+]">
									<input type="button" value="Вставить" onclick="BrowseServer('option_image[[+option.key+]]')">
								</label>
							</p>
							<p>
								[+lang.product_options_tip+] (<span class="tipid" id="info_tip_[+option.tip.id+]">[+option.tip.id+]</span>) <button class="tipclear" title="[+lang.product_options_tip_tips+]" name="tip_[+option.tip.id+]">[+lang.product_options_tip_new+]</button>
								<br>
								<input type="hidden" id="tip_[+option.tip.id+]" name="option_tip_id[[+option.key+]]" value="[+option.tip.id+]">
								<input type="text" class="option_tip_title" name="option_tip_title[[+option.key+]]" value="[+option.tip.title+]">
								<br>
								<textarea class="option_tip_description" name="option_tip_description[[+option.key+]]">[+option.tip.description+]</textarea>
							</p>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!--# move_form: Форма выбора нового родителя #-->
<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">[+lang.sbshop+]</a>
		&raquo;
		<span>[+lang.category_move+]</span>
	</div>
</div>
<div class="sectionBody">

	<h1>[+lang.category_move+]</h1>

	<form name="mutate" id="mutate" class="content" method="post" enctype="multipart/form-data" action="[+module.link+]&mode=cat&act=[+module.act+]">
		<input type="hidden" name="catid" value="[+category.id+]" />

		[+lang.category_parent_new+]:<br>
		<input type="text" name="parid" value="">
		<input type="submit" value="Перенести">
	</form>
</div>
