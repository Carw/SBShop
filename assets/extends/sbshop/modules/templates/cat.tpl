<!--# category_form: Форма редактирования раздела #-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-1.3.2.min.js\"></scr" + "ipt>" + "<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-ui-1.8.4.custom.min.js\"></scr" + "ipt>");
	}
</script>
<script type="text/javascript" src="[+site.url+]assets/extends/sbshop/modules/templates/js/category.js"></script>

<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">Электронный магазин</a>
		&raquo;
		<span>Редактирование категории</span>
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
			<li id="Button4"><a href="#" onclick="document.location.href='[+category.modulelink+]';"><img src="[+style.icons_cancel+]" />[+lang.cancel+]</a></li>
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
				Пока управление сделано в текстовом виде. Затем управление параметрами будет более умное.
				<div class="sectionHeader">[+lang.category_attributes+]</div>
				<div class="sectionBody">
					<div>[+category.attribute_tips+]</div>
					<div style="width:100%">
						<textarea id="ta" name="attributes" style="width:100%; height: 400px;" onchange="documentDirty=true;">[+category.attributes+]</textarea>
					</div>
				</div>
			</div>
			<div class="tab-page" id="tabFilter">
				<h2 class="tab">[+lang.category_tab_filter+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabFilter"));</script>
				<div class="sectionHeader">[+lang.category_filter+]</div>
				<div class="sectionBody">
					<div>[+category.attribute_tips+]</div>
					<div id="filter" style="width:100%">
						[+sb.filter+]
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
	</div>
	</form>
</div>
<!--# filter_outer #-->
<div class="filter">
	<input type="checkbox" class="on" name="filter_on[[+sb.id+]]" value="1" [+sb.on+]>
	<h3><a href="#">[+sb.title+]</a></h3>
	<div>
		<div>
			<select id="filter_type_[+sb.id+]" class="filter_type" name="filter_type[[+sb.id+]]">
				<option value="eqv" [+sb.eqv+]>[+lang.category_filter_type_evq+]</option>
				<option value="rng" [+sb.rng+]>[+lang.category_filter_type_rng+]</option>
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
			<button class="option_value_add" value="[+sb.id+]">[+lang.product_option_value_add+]</button>
		</div>
	</div>

</div>
<!--# filter_value #-->
<div class="values">
	<div class="col1">
		<input class="filter_value_title" style="width: 200px;" type="text" name="filter_value_title[[+sb.id+]][]" value="[+sb.value.title+]">
	</div>
	<div class="col3">
		<div class="rng [+sb.rng.visible+]">
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