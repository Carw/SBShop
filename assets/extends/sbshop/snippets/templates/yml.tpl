<!--# yml_outer: Общий контейнер #-->
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="[+sb.date+]">
    <shop>
		<name>[+sb.shop.name+]</name>
		<company>[+sb.shop.organization+]</company>
		<url>[(site_url)]</url>
        <platform>MODX</platform>
        <version>Evolution</version>
        <agency>ООО «Артима»</agency>
        <email>info@7plusov.ru</email>
		<currencies>
			<currency id="RUR" rate="1"/>
		</currencies>
		<categories>
			[+sb.categories+]
		</categories>
		<offers>
			[+sb.offers+]
		</offers>
	</shop>
</yml_catalog>
<!--# yml_category: Шаблон категории #-->
<category id="[+sb.id+]">[+sb.title+]</category>
<!--# yml_category_inner: Шаблон категории #-->
<category id="[+sb.id+]" parentId="[+sb.parent+]">[+sb.title+]</category>
<!--# yml_offer: Шаблон товара #-->
<offer id="[+sb.id+]" type="vendor.model" available="[+sb.existence+]" >
	<url>[+sb.url+]</url>
	<price>[+sb.price+]</price>
	<currencyId>RUR</currencyId>
	<categoryId>[+sb.category+]</categoryId>
	[+sb.images+]
    <store>false</store>
	<pickup>false</pickup>
	<delivery>true</delivery>
	<typePrefix>[+sb.param.группа+]</typePrefix>
	<vendor>[+sb.vendor+]</vendor>
	<vendorCode>[+sb.sku+]</vendorCode>
	<model>[+sb.title+]</model>
	<description>[+sb.introtext+]</description>
    <sales_notes>[+sb.salesnotes+]</sales_notes>
	<manufacturer_warranty>true</manufacturer_warranty>
	<country_of_origin>[+sb.param.страна+]</country_of_origin>
	[+sb.params+]
</offer>
<!--# yml_param: Шаблон опции #-->
<param name="[+sb.title+]" unit="[+sb.measure+]">[+sb.value+]</param>
<!--# yml_image: Шаблон опции #-->
<picture>[+sb.image+]</picture>