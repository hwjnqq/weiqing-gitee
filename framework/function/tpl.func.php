<?php
/**
 * 模板助手
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (defined('IN_MOBILE')) {
	load()->app('tpl');
} else {
	load()->web('tpl');
}

function tpl_form_field_date($name, $value = '', $withtime = false) {
	return _tpl_form_field_date($name, $value, $withtime);
}

function tpl_form_field_clock($name, $value = '') {
	$s = '';
	if(!defined('TPL_INIT_CLOCK_TIME')) {
		$s .= '
		<script type="text/javascript">
			require(["clockpicker"], function($){
				$(function(){
					$(".clockpicker").clockpicker({
						autoclose: true
					});
				});
			});
		</script>
		';
		define('TPL_INIT_CLOCK_TIME', 1);
	}
	$time = date('H:i');
	if(!empty($value)) {
		if(!strexists($value, ':')) {
			$time = date('H:i', $value);
		} else {
			$time = $value;
		}
	}
	$s .= '	<div class="input-group clockpicker">
				<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
				<input type="text" name="'.$name.'" value="'.$time.'" class="form-control">
			</div>';
	return $s;
}

/**
 * 【表单控件】: 范围日期选择器
 * @param string $name 表单input名称
 * @param array $value 表单input值
 * 		array('start' => 开始日期,'end' => 结束日期)
 * @param boolean $time 是否显示时间
 * @return string
 */
function tpl_form_field_daterange($name, $value = array(), $time = false) {
	$s = '';

	if (empty($time) && !defined('TPL_INIT_DATERANGE_DATE')) {
		$s = '
<script type="text/javascript">
	require(["daterangepicker"], function(){
		$(function(){
			$(".daterange.daterange-date").each(function(){
				var elm = this;
				$(this).daterangepicker({
					startDate: $(elm).prev().prev().val(),
					endDate: $(elm).prev().val(),
					format: "YYYY-MM-DD"
				}, function(start, end){
					$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());
					$(elm).prev().prev().val(start.toDateStr());
					$(elm).prev().val(end.toDateStr());
				});
			});
		});
	});
</script>
';
		define('TPL_INIT_DATERANGE_DATE', true);
	}

	if (!empty($time) && !defined('TPL_INIT_DATERANGE_TIME')) {
		$s = '
<script type="text/javascript">
	require(["daterangepicker"], function($){
		$(function(){
			$(".daterange.daterange-time").each(function(){
				var elm = this;
				$(this).daterangepicker({
					startDate: $(elm).prev().prev().val(),
					endDate: $(elm).prev().val(),
					format: "YYYY-MM-DD HH:mm",
					timePicker: true,
					timePicker12Hour : false,
					timePickerIncrement: 1,
					minuteStep: 1
				}, function(start, end){
					$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());
					$(elm).prev().prev().val(start.toDateTimeStr());
					$(elm).prev().val(end.toDateTimeStr());
				});
			});
		});
	});
</script>
';
		define('TPL_INIT_DATERANGE_TIME', true);
	}
	if ($value['starttime'] !== false && $value['start'] !== false) {
		if($value['start']) {
			$value['starttime'] = empty($time) ? date('Y-m-d',strtotime($value['start'])) : date('Y-m-d H:i',strtotime($value['start']));
		}
		$value['starttime'] = empty($value['starttime']) ? (empty($time) ? date('Y-m-d') : date('Y-m-d H:i') ): $value['starttime'];
	} else {
		$value['starttime'] = '请选择';
	}
	
	if ($value['endtime'] !== false && $value['end'] !== false) {
		if($value['end']) {
			$value['endtime'] = empty($time) ? date('Y-m-d',strtotime($value['end'])) : date('Y-m-d H:i',strtotime($value['end']));
		}
		$value['endtime'] = empty($value['endtime']) ? $value['starttime'] : $value['endtime'];
	} else {
		$value['endtime'] = '请选择';
	}
	$s .= '
	<input name="'.$name . '[start]'.'" type="hidden" value="'. $value['starttime'].'" />
	<input name="'.$name . '[end]'.'" type="hidden" value="'. $value['endtime'].'" />
	<button class="btn btn-default daterange '.(!empty($time) ? 'daterange-time' : 'daterange-date').'" type="button"><span class="date-title">'.$value['starttime'].' 至 '.$value['endtime'].'</span> <i class="fa fa-calendar"></i></button>
	';
	return $s;
}

/**
 * 【表单控件】: 出生日期控件
 * @param array $name 表单input名称
 * @param array $values 表单input值
 * @return string
 */
function tpl_form_field_calendar($name, $values = array()) {
	$html = '';
	if (!defined('TPL_INIT_CALENDAR')) {
		$html .= '
		<script type="text/javascript">
			function handlerCalendar(elm) {
				require(["moment"], function(moment){
					var tpl = $(elm).parent().parent();
					var year = tpl.find("select.tpl-year").val();
					var month = tpl.find("select.tpl-month").val();
					var day = tpl.find("select.tpl-day");
					day[0].options.length = 1;
					if(year && month) {
						var date = moment(year + "-" + month, "YYYY-M");
						var days = date.daysInMonth();
						for(var i = 1; i <= days; i++) {
							var opt = new Option(i, i);
							day[0].options.add(opt);
						}
						if(day.attr("data-value")!=""){
							day.val(day.attr("data-value"));
						} else {
							day[0].options[0].selected = "selected";
						}
					}
				});
			}
			require([""], function(){
				$(".tpl-calendar").each(function(){
					handlerCalendar($(this).find("select.tpl-year")[0]);
				});
			});
		</script>';
		define('TPL_INIT_CALENDAR', true);
	}

	if (empty($values) || !is_array($values)) {
		$values = array(0,0,0);
	}
	$values['year'] = intval($values['year']);
	$values['month'] = intval($values['month']);
	$values['day'] = intval($values['day']);

	if (empty($values['year'])) {
		$values['year'] = '1980';
	}
	$year = array(date('Y'), '1914');
	$html .= '<div class="row row-fix tpl-calendar">
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<select name="' . $name . '[year]" onchange="handlerCalendar(this)" class="form-control tpl-year">
				<option value="">年</option>';
	for ($i = $year[1]; $i <= $year[0]; $i++) {
		$html .= '<option value="' . $i . '"' . ($i == $values['year'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
	}
	$html .= '	</select>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<select name="' . $name . '[month]" onchange="handlerCalendar(this)" class="form-control tpl-month">
				<option value="">月</option>';
	for ($i = 1; $i <= 12; $i++) {
		$html .= '<option value="' . $i . '"' . ($i == $values['month'] ? ' selected="selected"' : '') . '>' . $i . '</option>';
	}
	$html .= '	</select>
		</div>
		<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
			<select name="' . $name . '[day]" data-value="' . $values['day'] . '" class="form-control tpl-day">
				<option value="0">日</option>
			</select>
		</div>
	</div>';
	return $html;
}

/**
 * 【表单控件】: 省市县(区)地区选择器
 * @param array $name 表单input名称
 * 		  默认为$names['province'] = 'province',
 * 		  $names['city'] = 'city, $names['district'] = 'district'
 * @param array $values 表单input值
 * @return string
 */
function tpl_form_field_district($name, $values = array()) {
	$html = '';
	if (!defined('TPL_INIT_DISTRICT')) {
		$html .= '
		<script type="text/javascript">
			require(["district"], function(dis){
				$(".tpl-district-container").each(function(){
					var elms = {};
					elms.province = $(this).find(".tpl-province")[0];
					elms.city = $(this).find(".tpl-city")[0];
					elms.district = $(this).find(".tpl-district")[0];
					var vals = {};
					vals.province = $(elms.province).attr("data-value");
					vals.city = $(elms.city).attr("data-value");
					vals.district = $(elms.district).attr("data-value");
					dis.render(elms, vals, {withTitle: true});
				});
			});
		</script>';
		define('TPL_INIT_DISTRICT', true);
	}
	if (empty($values) || !is_array($values)) {
		$values = array('province'=>'','city'=>'','district'=>'');
	}
	if(empty($values['province'])) {
		$values['province'] = '';
	}
	if(empty($values['city'])) {
		$values['city'] = '';
	}
	if(empty($values['district'])) {
		$values['district'] = '';
	}
	$html .= '
		<div class="row row-fix tpl-district-container">
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
				<select name="' . $name . '[province]" data-value="' . $values['province'] . '" class="form-control tpl-province">
				</select>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
				<select name="' . $name . '[city]" data-value="' . $values['city'] . '" class="form-control tpl-city">
				</select>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
				<select name="' . $name . '[district]" data-value="' . $values['district'] . '" class="form-control tpl-district">
				</select>
			</div>
		</div>';
	return $html;
}

/**
 * 二级分类选择器
 * @param string $name 表单名称
 * @param array $parents 父分类,
 * @param array $children 子分类,
 * @param int $parentid 选择的父 id
 * @param int $childid 选择的子id
 * @return string Html代码
 */
function tpl_form_field_category_2level($name, $parents, $children, $parentid, $childid){
	$html = '
		<script type="text/javascript">
			window._' . $name . ' = ' . json_encode($children) . ';
		</script>';
			if (!defined('TPL_INIT_CATEGORY')) {
				$html .= '
		<script type="text/javascript">
			function renderCategory(obj, name){
				var index = obj.options[obj.selectedIndex].value;
				require([\'jquery\', \'util\'], function($, u){
					$selectChild = $(\'#\'+name+\'_child\');
					var html = \'<option value="0">请选择二级分类</option>\';
					if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
						$selectChild.html(html);
						return false;
					}
					for(var i=0; i< window[\'_\'+name][index].length; i++){
						html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
					}
					$selectChild.html(html);
				});
			}
		</script>
					';
				define('TPL_INIT_CATEGORY', true);
			}

			$html .=
				'<div class="row row-fix tpl-category-container">
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
				<select class="form-control tpl-category-parent we7-select" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategory(this,\'' . $name . '\')">
					<option value="0">请选择一级分类</option>';
			$ops = '';
			if(!empty($parents)) {
				foreach ($parents as $row) {
					$html .= '
						<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
				}
			}
			
			$html .= '
				</select>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
				<select class="form-control tpl-category-child we7-select" id="' . $name . '_child" name="' . $name . '[childid]">
					<option value="0">请选择二级分类</option>';
			if (!empty($parentid) && !empty($children[$parentid])) {
				foreach ($children[$parentid] as $row) {
					$html .= '
					<option value="' . $row['id'] . '"' . (($row['id'] == $childid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
				}
			}
			$html .= '
				</select>
			</div>
		</div>
	';
	return $html;
}

/**
 *【表单控件】: 行业分类选择器
 * @param string $name  表单名称
 * @param string $pvalue 父类选中元素
 * @param string $cvalue 子类选中元素
 * @param string $parentid select 父类id
 * @param string $childid  select 子类id
 * @return string
 */
function tpl_form_field_industry($name, $pvalue = '', $cvalue = '', $parentid = 'industry_1', $childid = 'industry_2'){
	$html = '
	<div class="row row-fix">
		<div class="col-sm-4">
			<select name="' . $name . '[parent]" id="' . $parentid . '" class="form-control" value="' . $pvalue . '"></select>
		</div>
		<div class="col-sm-4">
			<select name="' . $name . '[child]" id="' . $childid . '" class="form-control" value="' . $cvalue . '"></select>
		</div>
		<script type="text/javascript">
			require([\'industry\'], function(industry){
				industry.init("'. $parentid . '","' . $childid . '");
			});
		</script>
	</div>';
	return $html;
}

/**
 * 【表单控件】: 地理位置选择器
 * @param string $field 表单中input名称
 * @param array $value 表单中input值
 * 		$value['lat']，$value['lng']
 * @return string
 */
function tpl_form_field_coordinate($field, $value = array()) {
	$s = '';
	if(!defined('TPL_INIT_COORDINATE')) {
		$s .= '<script type="text/javascript">
				function showCoordinate(elm) {
					require(["util"], function(util){
						var val = {};
						val.lng = parseFloat($(elm).parent().prev().prev().find(":text").val());
						val.lat = parseFloat($(elm).parent().prev().find(":text").val());
						util.map(val, function(r){
							$(elm).parent().prev().prev().find(":text").val(r.lng);
							$(elm).parent().prev().find(":text").val(r.lat);
						});

					});
				}

			</script>';
		define('TPL_INIT_COORDINATE', true);
	}
	$s .= '
		<div class="row row-fix">
			<div class="col-xs-4 col-sm-4">
				<input type="text" name="' . $field . '[lng]" value="'.$value['lng'].'" placeholder="地理经度"  class="form-control" />
			</div>
			<div class="col-xs-4 col-sm-4">
				<input type="text" name="' . $field . '[lat]" value="'.$value['lat'].'" placeholder="地理纬度"  class="form-control" />
			</div>
			<div class="col-xs-4 col-sm-4">
				<button onclick="showCoordinate(this);" class="btn btn-default" type="button">选择坐标</button>
			</div>
		</div>';
	return $s;
}

/**
 * 【表单控件】: 系统预设表单
 * @param string $field 表单input名称
 * 	表单类型:
 * <pre>
 * 	'avatar':上传头像
 * 	'gender':获取性别
 * 	'birth' :获取生日
 * 	'reside':获取地区
 * 	'education':获取学历
 * 	'constellation':获取星座
 * 	'zodiac':获取生肖
 * 	'bloodtype':获取血型
 * </pre>
 * @param mixed $value 表单input值
 * @return string
 */
function tpl_fans_form($field, $value = '') {
	switch ($field) {
	case 'avatar':
		$avatar_url = '../attachment/images/global/avatars/';
		$html = '';
		if (!defined('TPL_INIT_AVATAR')) {
			$html .= '
			<script type="text/javascript">
				function showAvatarDialog(elm, opts) {
					require(["util"], function(util){
						var btn = $(elm);
						var ipt = btn.parent().prev();
						var img = ipt.parent().next().children();
						var content = \'<div class="avatar-browser clearfix">\';
						for(var i = 1; i <= 12; i++) {
							content +=
								\'<div title="头像\' + i + \'" class="thumbnail">\' +
									\'<em><img src="' . $avatar_url . 'avatar_\' + i + \'.jpg" class="img-responsive"></em>\' +
								\'</div>\';
						}
						content += "</div>";
						var dialog = util.dialog("请选择头像", content);
						dialog.modal("show");
						dialog.find(".thumbnail").on("click", function(){
							var url = $(this).find("img").attr("src");
							img.get(0).src = url;
							ipt.val(url.replace(/^\.\.\/attachment\//, ""));
							dialog.modal("hide");
						});
					});
				}
			</script>';
			define('TPL_INIT_AVATAR', true);
		}
		if (!defined('TPL_INIT_IMAGE')) {
			global $_W;
			if (defined('IN_MOBILE')) {
				$html .= <<<EOF
				<script type="text/javascript">
					// in mobile
					function showImageDialog(elm) {
						require(["jquery", "util"], function($, util){
							var btn = $(elm);
							var ipt = btn.parent().prev();
							var val = ipt.val();
							var img = ipt.parent().next().children();
							util.image(elm, function(url){
								img.get(0).src = url.url;
								ipt.val(url.attachment);
							});
						});
					}
				</script>
EOF;
			} else {
				$html .= <<<EOF
				<script type="text/javascript">
					// in web
					function showImageDialog(elm, opts) {
						require(["util"], function(util){
							var btn = $(elm);
							var ipt = btn.parent().prev();
							var val = ipt.val();
							var img = ipt.parent().next().find('img');
							util.image(val, function(url){
								img.get(0).src = url.url;
								ipt.val(url.attachment);
							}, {multiple:false,type:"image",direct:true}, opts);
						});
					}
				</script>
EOF;
			}
			define('TPL_INIT_IMAGE', true);
		}
		$val = './resource/images/nopic.jpg';
		if (!empty($value)) {
			$val = tomedia($value);
		}
		$options = array();
		$options['width'] = '200';
		$options['height'] = '200';

		if (defined('IN_MOBILE')) {
			$html .= <<<EOF
			<div class="input-group">
				<input type="text" value="{$value}" name="{$field}" class="form-control" autocomplete="off">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="showImageDialog(this);">选择图片</button>
					<button class="btn btn-default" type="button" onclick="showAvatarDialog(this);">系统头像</button>
				</span>
			</div>
			<div class="input-group" style="margin-top:.5em;">
				<img src="{$val}" class="img-responsive img-thumbnail" width="150" style="max-height: 150px;"/>
			</div>
EOF;
		} else {
			$html .= '
			<div class="input-group">
				<input type="text" value="' . $value . '" name="' . $field . '" class="form-control" autocomplete="off">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="showImageDialog(this, \'' . base64_encode(iserializer($options)) . '\');">选择图片</button>
					<button class="btn btn-default" type="button" onclick="showAvatarDialog(this);">系统头像</button>
				</span>
			</div>
			<div class="input-group" style="margin-top:.5em;">
				<img src="' . $val . '" class="img-responsive img-thumbnail" width="150" />
			</div>';
		}

		break;
	case 'birth':
	case 'birthyear':
	case 'birthmonth':
	case 'birthday':
		$html = tpl_form_field_calendar('birth', $value);
		break;
	case 'reside':
	case 'resideprovince':
	case 'residecity':
	case 'residedist':
		$html = tpl_form_field_district('reside', $value);
		break;
	case 'bio':
	case 'interest':
		$html = '<textarea name="' . $field . '" class="form-control">' . $value . '</textarea>';
		break;
	case 'gender':
		$html = '
				<select name="gender" class="form-control">
					<option value="0" ' . ($value == 0 ? 'selected ' : '') . '>保密</option>
					<option value="1" ' . ($value == 1 ? 'selected ' : '') . '>男</option>
					<option value="2" ' . ($value == 2 ? 'selected ' : '') . '>女</option>
				</select>';
		break;
	case 'education':
	case 'constellation':
	case 'zodiac':
	case 'bloodtype':
		if ($field == 'bloodtype') {
			$options = array('A', 'B', 'AB', 'O', '其它');
		} elseif ($field == 'zodiac') {
			$options = array('鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪');
		} elseif ($field == 'constellation') {
			$options = array('水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座', '狮子座', '处女座', '天秤座', '天蝎座', '射手座', '摩羯座');
		} elseif ($field == 'education') {
			$options = array('博士', '硕士', '本科', '专科', '中学', '小学', '其它');
		}
		$html = '<select name="' . $field . '" class="form-control">';
		foreach ($options as $item) {
			$html .= '<option value="' . $item . '" ' . ($value == $item ? 'selected ' : '') . '>' . $item . '</option>';
		}
		$html .= '</select>';
		break;
	case 'nickname':
	case 'realname':
	case 'address':
	case 'mobile':
	case 'qq':
	case 'msn':
	case 'email':
	case 'telephone':
	case 'taobao':
	case 'alipay':
	case 'studentid':
	case 'grade':
	case 'graduateschool':
	case 'idcard':
	case 'zipcode':
	case 'site':
	case 'affectivestatus':
	case 'lookingfor':
	case 'nationality':
	case 'height':
	case 'weight':
	case 'company':
	case 'occupation':
	case 'position':
	case 'revenue':
	default:
		$html = '<input type="text" class="form-control" name="' . $field . '" value="' . $value . '" />';
		break;
	}
	return $html;
}