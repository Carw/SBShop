<!--# product: Общий шаблон редактирования товара #-->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
	if(typeof(jQuery) == "undefined") {
		document.write("<scr" + "ipt type=\"text/javascript\" src=\"' . MODX_SITE_URL . 'assets/libs/javascript/jquery-1.3.2.min.js\"></scr" + "ipt>");
	}
</script>
<script type="text/javascript" src="[+site.url+]assets/libs/javascript/jquery.dragsort-0.4.3.min.js"></script>
<script type="text/javascript" src="[+site.url+]assets/extends/sbshop/modules/templates/js/product.js?ver=0.3.1"></script>
<link href="[+site.url+]assets/libs/javascript/css/fileuploader.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="[+site.url+]assets/libs/javascript/fileuploader.js"></script>

<div class="sectionHeader">
	<div class="breadcrumbs">
		<a href="[+module.link+]">[+lang.sbshop+]</a>
		&raquo;
		<span>Редактирование товара</span>
	</div>
</div>
<div class="sectionBody">

	<h1>[+lang.product_edit+]</h1>

	<form name="mutate" id="mutate" class="content" method="post" enctype="multipart/form-data" action="[+module.link+]&mode=prod&act=[+module.act+]">
	<input type="hidden" id="prodid" name="prodid" value="[+product.id+]" />
	<input type="hidden" name="catid" value="[+product.category+]" />
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

	[+product.error+]

	<div class="sectionBody">
		<div class="tab-pane" id="docManagerPane">
			<script type="text/javascript">
				tpResources = new WebFXTabPane(document.getElementById("docManagerPane"));
			</script>

			<div class="tab-page" id="tabGeneral">
				<h2 class="tab">[+lang.product_tab_general+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabGeneral"));</script>
				<table width="99%" border="0" cellspacing="5" cellpadding="0">
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_title+]</span></td>
						<td><input name="title" type="text" maxlength="255" value='[+product.title+]' class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_title_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_longtitle+]</span></td>
						<td><input name="longtitle" type="text" maxlength="255" value='[+product.longtitle+]' class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_longtitle_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_model+]</span></td>
						<td><input name="model" type="text" maxlength="255" value='[+product.model+]' class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_model_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_alias+]</span></td>
						<td><input name="alias" type="text" maxlength="255" value="[+product.alias+]" class="inputBox" onchange="documentDirty=true;" spellcheck="true" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_alias_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_sku+]</span></td>
						<td><input name="sku" type="text" maxlength="255" value="[+product.sku+]" class="inputBox" onchange="documentDirty=true;" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_sku_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_price+]</span></td>
						<td><input name="price" type="text" maxlength="255" value="[+product.price+]" class="inputBox" onchange="documentDirty=true;" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_price_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_price_add+]</span></td>
						<td><input name="price_add" type="text" maxlength="255" value="[+product.price_add+]" class="inputBox" onchange="documentDirty=true;" />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_price_add_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_vendor+]</span></td>
						<td><input name="vendor" type="text" maxlength="255" value="[+product.vendor+]" class="inputBox" onchange="documentDirty=true;" spellcheck="true" [+product.group+] />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_vendor_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_published+]</span></td>
						<td><input name="published" type="checkbox" maxlength="255" value="1" class="inputBox" onchange="documentDirty=true;" spellcheck="true" [+product.published+] />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_published_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
					<tr style="height: 24px;"><td width="100" align="left"><span class="warning">[+lang.product_existence+]</span></td>
						<td><input name="existence" type="checkbox" maxlength="255" value="1" class="inputBox" onchange="documentDirty=true;" spellcheck="true" [+product.existence+] />
						&nbsp;&nbsp;<img src="[+style.icons_tooltip_over+]" onmouseover="this.src='[+style.icons_tooltip+]';" onmouseout="this.src='[+style.icons_tooltip_over+]';" alt="[+lang.product_existence_description+]" onclick="alert(this.alt);" style="cursor:help;" /></td></tr>
				</table>

				<div class="sectionHeader">[+lang.product_introtext+]</div>
				<div class="sectionBody">
					<div style="width:100%">
						<textarea name="introtext" style="width:100%; height: 100px;" onchange="documentDirty=true;">[+product.introtext+]</textarea>
					</div>
				</div>

				<div class="sectionHeader">[+lang.product_description+]</div>
				<div class="sectionBody">
					<div style="width:100%">
						<textarea name="description" style="width:100%; height: 400px;" onchange="documentDirty=true;">[+product.description+]</textarea>
					</div>
				</div>
			</div>
			<div class="tab-page" id="tabImages">
				<h2 class="tab">[+lang.product_tab_images+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabImages"));</script>
				<div id="images" style="width:100%">
					<div id="file-uploader">
					    <noscript>
					        <p>Please enable JavaScript to use file uploader.</p>
					        <!-- or put a simple form for upload here -->
					    </noscript>
					</div>

					<div id="imagebox" class="imagebox">
						[+images+]
					</div>

					<div id="file-uploader-ext">
					    <noscript>
					        <p>Please enable JavaScript to use file uploader.</p>
					        <!-- or put a simple form for upload here -->
					    </noscript>
					</div>

					<div id="filebox" class="filebox">
						[+files+]
					</div>
				</div>
			</div>
			<div class="tab-page" id="tabAttributes">
				<h2 class="tab">[+lang.product_tab_attributes+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabAttributes"));</script>
				<div id="attributes" style="width:100%">
					<div class="attribute_group opened">
						<div class="header">
							<div class="opener"></div>
							<div class="attribute_group_name">Общая группа</div>
						</div>
						<div class="attribute_group_outer">
							[+attributes+]
						</div>
						<button class="new_attribute_add button_add">[+lang.product_attribute_add+]</button>
					</div>
				</div>
				<div class="templates">
					<div class="attribute attribute_template">
						<div class="attributedel">
							<input type="image" class="attribute_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
						</div>
						<input type="text" class="attribute_title" name="attribute_name[]">
						<input type="text" class="attribute_measure" name="attribute_measure[]">
						<input type="text" class="attribute_value" name="attribute_value[]">
						<select name="attribute_type[]">
							<option value="n" selected="selected">[+lang.product_attribute_type_normal+]</option>
							<option value="p" style="color: green;">[+lang.product_attribute_type_primary+]</option>
							<option value="h" style="color: silver;">[+lang.product_attribute_type_hidden+]</option>
						</select>
					</div>
				</div>
				<script type="text/javascript">
					// Заголовок комплектации по умолчанию
					attribute_name = '[+lang.product_attribute_name+]';
				</script>
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
				<div class="templates">
					<div class="values value_template">
						<div class="col1">
							<input type="hidden" class="option_value_ids" value="">
							<input type="text" style="width: 200px;" class="option_values_title" value=""> <span class="id">(*)</span>
						</div>
						<div class="col2">
							<input type="text" style="width: 100px;" class="option_values_value" value="">
						</div>
						<div class="del">
							<input type="image" style="width: auto;" class="option_value_del" src="/manager/media/style/MODxCarbon/images/icons/delete.png" title="[+lang.product_option_del_hint+]">
							<input type="image" style="width: auto;" class="option_value_extend" src="/manager/media/style/MODxCarbon/images/icons/table.gif" title="[+lang.product_option_extend_hint+]">
						</div>
						<div class="extend">
							<p class="title">[+lang.product_option_extend_add+]</p>
							<input type="text" class="option_values_add" value="">
							<p class="title">[+lang.product_option_extend_class+]</p>
							<input type="text" class="option_values_class" value="">
							<p class="title">[+lang.product_option_extend_image+]</p>
							<input type="text" class="option_values_image" value="">
						</div>
					</div>
				</div>
				<script type="text/javascript">
					// Заголовок комплектации по умолчанию
					option_name = '[+lang.product_option_name+]';
				</script>
			</div>
			<div class="tab-page" id="tabBundles">
				<h2 class="tab">[+lang.product_tab_bundles+]</h2>
				<script type="text/javascript">tpResources.addTabPage(document.getElementById("tabBundles"));</script>
				<div id="bundles" style="width:100%">
					<table class="bundle_template">
						<tr class="bundle">
							<td class="dragHandle"></td>
							<td>
								<table class="inner">
									<tr class="bundle_header">
										<td class="header">
											<h3 class="title"></h3>
										</td>
										<td class="actions">
											<div class="bundledel">
												<input type="image" class="bundle_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
											</div>
										</td>
									</tr>
									<tr class="content">
										<td colspan="2">
											<div>
												<div>
													<p>
														<label>
															[+lang.product_bundle_title+]<br>
															<input type="text" class="bundle_name" name="bundle_name[]" value="">
														</label>
													</p>
													<p>
														<label>
															[+lang.product_bundle_price+]<br>
															<input type="text" style="width: 50px;" name="bundle_price[]" value="">
														</label>
													</p>
													<p>
														<label>
															[+lang.product_bundle_price_add+]<br>
															<input type="text" style="width: 50px;" name="bundle_price_add[]" value="">
														</label>
													</p>
													<p>
														<label>
															[+lang.product_bundle_settings+]<br>
															<textarea name="bundle_settings[]"></textarea>
														</label>
													</p>
													<p>
														<label>
															[+lang.product_bundle_description+]<br>
															<textarea name="bundle_description[]"></textarea>
														</label>
													</p>
												</div>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<table class="sorttable">
						<tr class="bundle_base">
							<td class="nodrag"></td>
							<td>
								<table class="inner">
									<tr class="bundle_header">
										<td class="header">
											<h3 class="title">[+lang.product_bundle_base+]</h3>
										</td>
									</tr>
									<tr class="content">
										<td>
											<div>
												<p>
													<label>
														[+lang.product_bundle_settings+]<br>
														<textarea name="bundle_base_settings">[+product.base_bundle+]</textarea>
													</label>
												</p>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						[+bundles+]
					</table>
				</div>
				<div class="bundle_toolbar">
					<p>
						<label>
							<input type="text" id="new_bundle_name" value=""> <button class="button_add" id="new_bundle_add">[+lang.product_bundle_add+]</button>
						</label>
					</p>
					<p>
						<label>
							<input type="checkbox" class="option_bundle_personal" name="bundle_personal" value="1" [+bundle.personal.checked+]> [+lang.product_bundles_personal+]
						</label>
					</p>
					<div class="split"></div>
				</div>
				<div class="personal_bundle">
					<h3>Индивидуальная комплектация:</h3>
					<p>
						<input type="text" name="personal_bundle_settings" id="personal_bundle" value="[+product.personal_bundle+]">
					</p>
				</div>
				<script type="text/javascript">
					// Заголовок комплектации по умолчанию
					bundle_name = '[+lang.product_bundle_name+]';
				</script>
			</div>
		</div>
	</div>
	</form>
</div>
<!--# bundles: Шаблон управления комплектациями #-->
<tr class="bundle">
	<td class="dragHandle"></td>
	<td>
		<table class="inner">
			<tr class="bundle_header">
				<td class="header">
					<h3 class="title">[+bundle_name+]</h3>
				</td>
				<td class="actions">
					<div class="bundledel">
						<input type="image" class="bundle_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
					</div>
				</td>
			</tr>
			<tr class="content">
				<td colspan="2">
					<div>
						<p>
							<label>
								[+lang.product_bundle_title+]<br>
								<input type="text" class="bundle_name" name="bundle_name[]" value="[+bundle_name+]">
							</label>
						</p>
						<p>
							<label>
								[+lang.product_bundle_price+]<br>
								<input type="text" style="width: 50px;" name="bundle_price[]" value="[+bundle_price+]">
							</label>
						</p>
						<p>
							<label>
								[+lang.product_bundle_price_add+]<br>
								<input type="text" style="width: 50px;" name="bundle_price_add[]" value="[+bundle_price_add+]">
							</label>
						</p>
						<p>
							<label>
								[+lang.product_bundle_settings+]<br>
								<textarea name="bundle_settings[]">[+bundle_settings+]</textarea>
							</label>
						</p>
						<p>
							<label>
								[+lang.product_bundle_description+]<br>
								<textarea name="bundle_description[]">[+bundle_description+]</textarea>
							</label>
						</p>
					</div>
				</td>
			</tr>
		</table>
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
						<input type="hidden" name="option_id[]" value="[+option.id+]">
						<p>
							<label>
								[+lang.product_options_title+]<br>
								<input type="text" class="option_name" name="option_name[]" value="[+option.title+]">
							</label>
						</p>
						<div class="optiondel">
							<input type="image" style="width: auto;" class="option_extend" src="/manager/media/style/MODxCarbon/images/icons/table.gif">
						</div>
						<div class="extend">
							<p>
								<label>
									[+lang.product_options_longtitle+]<br>
									<input type="text" class="option_longname" name="option_longname[[+option.id+]]" value="[+option.longtitle+]">
								</label>
							</p>
							<p>
								<label>
									[+lang.product_options_class+]<br>
									<input type="text" class="option_class" name="option_class[[+option.id+]]" value="[+option.class+]">
								</label>
							</p>
							<p>
								<label>
									[+lang.product_options_image+]<br>
									<input type="text" class="option_image" id="option_image_[+option.id+]]" name="option_image[[+option.id+]]" value="[+option.image+]">
									<input type="button" value="Вставить" onclick="BrowseServer('option_image[[+option.id+]]')">
								</label>
							</p>
							<p>
								<label>
									<input type="checkbox" class="option_notbundle" name="option_notbundle[[+option.id+]]" value="[+option.id+]" [+option.notbundle.checked+]> [+lang.product_options_notbundle+]
								</label>
							</p>
							<p>
								<label>
									<input type="checkbox" class="option_hidden" name="option_hidden[[+option.id+]]" value="[+option.id+]" [+option.hidden.checked+]> [+lang.product_options_hidden+]
								</label>
							</p>
							<p>
								[+lang.product_options_tip+] (<span class="tipid" id="info_tip_[+option.tip.id+]">[+option.tip.id+]</span>) <button class="tipclear" title="[+lang.product_options_tip_tips+]" name="tip_[+option.tip.id+]">[+lang.product_options_tip_new+]</button>
								<br>
								<input type="hidden" id="tip_[+option.tip.id+]" name="option_tip_id[[+option.id+]]" value="[+option.tip.id+]">
								<input type="text" class="option_tip_title" name="option_tip_title[[+option.id+]]" value="[+option.tip.title+]">
								<br>
								<textarea class="option_tip_description" name="option_tip_description[[+option.id+]]">[+option.tip.description+]</textarea>
							</p>
						</div>
						<div class="split"></div>
						<div id="values_[+option.id+]">
							<div class="values th">
								<div class="col1">[+lang.product_option_title+]</div>
								<div class="col2">[+lang.product_option_value+]</div>
							</div>
							[+sb.wrapper+]
						</div>
						<div>
							<button class="option_value_add button_add" value="[+option.id+]">[+lang.product_option_value_add+]</button>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
<!--# option_row: Шаблон опции #-->
<div class="values">
	<div class="col1">
		<input type="hidden" name="option_value_ids[[+option.id+]][]" value="[+value.id+]">
		<input type="text" style="width: 200px;" name="option_values_title[[+option.id+]][]" value="[+value.title+]"> <span class="id">([+value.id+])</span>
	</div>
	<div class="col2">
		<input type="text" style="width: 100px;" name="option_values_value[[+option.id+]][]" value="[+value.value+]">
	</div>
	<div class="del">
		<input type="image" style="width: auto;" class="option_value_del" src="/manager/media/style/MODxCarbon/images/icons/delete.png" title="[+lang.product_option_del_hint+]">
		<input type="image" style="width: auto;" class="option_value_extend" src="/manager/media/style/MODxCarbon/images/icons/table.gif" title="[+lang.product_option_extend_hint+]">
	</div>
	<div class="extend">
		<p class="title">[+lang.product_option_extend_add+]</p>
		<input type="text" name="option_values_add[[+option.id+]][]" value="[+value.price_add+]">
		<p class="title">[+lang.product_option_extend_class+]</p>
		<input type="text" name="option_values_class[[+option.id+]][]" value="[+value.class+]">
		<p class="title">[+lang.product_option_extend_image+]</p>
		<input type="text" name="option_values_image[[+option.id+]][]" value="[+value.image+]">
	</div>
</div>
<!--# attribute_outer: Контейнер для параметров #-->
<div class="attribute">
	<input type="hidden" name="attribute_id[]" value="[+attribute.id+]">
	<div class="attributedel">
		<input type="image" class="attribute_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
	</div>
	<input type="text" class="attribute_title" name="attribute_name[]" value="[+attribute.title+]">
	<input type="text" class="attribute_measure" name="attribute_measure[]" value="[+attribute.measure+]">
	<input type="text" class="attribute_value" name="attribute_value[]" value="[+attribute.value+]">
	<select name="attribute_type[]">
		<option value="n" [+attribute.type.normal+]>[+lang.product_attribute_type_normal+]</option>
		<option value="p" style="color: green" [+attribute.type.primary+]>[+lang.product_attribute_type_primary+]</option>
		<option value="h" style="color: silver;" [+attribute.type.hidden+]>[+lang.product_attribute_type_hidden+]</option>
	</select>
</div>
<!--# image_row: Шаблон изображения #-->
<div class="img" id="img-[+sb.id+]" style="background-image: url('[+sb.image+]')">
	<div class="imagedel">
		<input type="image" class="image_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
	</div>
	<input type="hidden" class="image_id" name="img[]" value="[+sb.id+]" />
</div>
<!--# file_row: Шаблон файла #-->
<div class="file file_[+sb.type+]" id="file-[+sb.id+]">
	<div class="filedel">
		<input type="image" class="file_del" style="width: auto;" src="/manager/media/style/MODxCarbon/images/icons/delete.png">
	</div>
	<a href="[+sb.file+]" title="[+sb.name+]">Link</a>
	<input type="hidden" class="file_id" name="file[]" value="[+sb.id+]" />
</div>