<?php
class htmlWidget {

	protected $element;

	public function __call($function, $args) {
		$return = call_user_func_array(array($this->element, $function), $args);
		if (!is_object($return)){
			return $return;
		}
		return $this;
	}

	public function draw(){
		return $this->element->draw();
	}

	public function setId($val){
		$this->element->attr('id', $val);
		return $this;
	}

	public function setName($val){
		$this->element->attr('name', $val);
		return $this;
	}

	public function startChain(){
		return $this;
	}
}
/**
 * System Link Menu Widget Class
 * @package Html
 */
class htmlWidget_systemLinkMenu extends htmlWidget
{

	protected $settings = array();

	protected $selectElement;

	public function __construct($options = array()) {
		$this->settings = array_merge(array(
			'allowNoLink' => true,
			'allowAppLink' => true,
			'allowCatLink' => true,
			'allowCustomLink' => true,
			'allowSameWindowTarget' => true,
			'allowNewWindowTarget' => true,
			'allowJqueryDialogTarget' => true,
			'excludedApps' => array(),
			'excludedCats' => array(),
			'data' => false
		), $options);

		$this->element = htmlBase::newElement('selectbox');
	}

	public function draw() {
		global $App;
		$appArr = array();
		if ($this->settings['allowAppLink'] === true){
			$appArr = $App->getApplications($this->settings['excludedApps']);
		}

		$catArr = array();
		if ($this->settings['allowCatLink'] === true){
			$Categories = Doctrine_Core::getTable('Categories')->getRecordInstance();
			$catArr = $Categories->getCategories($this->settings['excludedCats']);
		}

		$linkTargets = array();
		if ($this->settings['allowSameWindowTarget'] === true){
			$linkTargets['none'] = '-- Link Target --';
			$linkTargets['same'] = 'Same Window';
		}
		if ($this->settings['allowNewWindowTarget'] === true){
			$linkTargets['new'] = 'New Window';
		}
		if ($this->settings['allowJqueryDialogTarget'] === true){
			$linkTargets['dialog'] = 'jQuery Dialog';
		}

		$linkTypes = array();
		if ($this->settings['allowNoLink'] === true){
			$linkTypes['none'] = 'No Link';
		}
		if ($this->settings['allowAppLink'] === true){
			$linkTypes['app'] = 'Application';
		}
		if ($this->settings['allowCatLink'] === true){
			$linkTypes['category'] = 'Category';
		}
		if ($this->settings['allowCustomLink'] === true){
			$linkTypes['custom'] = 'Custom';
		}

		$this->element->addClass('systemLinkType');
		if ($this->settings['data'] !== false){
			$this->element->selectOptionByValue($this->settings['data']->type);
		}
		foreach($linkTypes as $k => $v){
			$this->element->addOption($k, $v);
		}

		$javascript = '';
		if ($App->hasAddedJavascript(__CLASS__) === false){
			ob_start();
			?>
		<script>
			var appJson = <?php echo json_encode($appArr);?>;
			var catJson = <?php echo json_encode($catArr);?>;
			var menuLinkTypes = <?php echo json_encode($linkTypes);?>;
			var menuLinkTargets = <?php echo json_encode($linkTargets);?>;

			$.newSystemLinkMenu = function ($appendTo, name){
				var inputKey = 0;
				while($('.systemLinkType[data-input_key=' + inputKey + ']').size() > 0){
					inputKey++;
				}

				var newSelect = $('<select></select>')
					.attr('name', name + '[' + inputKey + ']')
					.attr('data-input_key', inputKey)
					.addClass('systemLinkType');

				$.each(menuLinkTypes, function (k, v) {
					newSelect.append('<option value="' + k + '">' + v + '</option>');
				});

				if (!$appendTo){
					return $('<div></div>').append(newSelect).html();
				}else{
					$appendTo.find('.systemLinkMenuContainer')
						.append(newSelect);
				}
			};

			$(document).ready(function () {
				$('.systemLinkType').live('change', function () {
					var name = $(this).attr('name');
					name = name.replace('[type]', '');

					$(this).parent().find('.linkFields').remove();
					if ($(this).val() == 'app'){
						var options = '<option value="none">-- Application --</option>';
						$.each(appJson, function (appName, pages) {
							if (appName == 'ext'){
								return;
							}

							options = options + '<option value="' + appName + '">' + appName + '</option>';
						});
						$.each(appJson.ext, function (extName, Apps) {
							$.each(Apps, function (appName, pages) {
								var val = extName + '/' + appName;
								var text = extName + ' > ' + appName;
								options = options + '<option value="' + val + '">' + text + '</option>';
							});
						});
						var field = '<select name="' + name + '[app][name]" class="systemLinkApp linkFields" style="display:block"></select>';
						var targetType = 'app';
					} else if ($(this).val() == 'category'){
						var options = '<option value="none">-- Category --</option>';
						$.each(catJson[0].children, function (categoryId, cInfo) {
							options = options + '<option value="' + categoryId + '" data-has_child="' + (cInfo.children ? 'true' : 'false') + '">' + cInfo.name + '</option>';
						});
						var field = '<select name="' + name + '[category][id][]" class="systemLinkCategory linkFields" style="display:block"></select>';
						var targetType = 'category';
					}
					else {
						if ($(this).val() == 'custom'){
							var field = '<input type="text" name="' + name + '[custom][url]" class="linkFields" style="display:block">';
							var targetType = 'custom';
						}
					}

					if ($(this).val() != 'none'){
						$(field).append(options).appendTo($(this).parent());

						var menuLinkTargetOptions = '';
						$.each(menuLinkTargets, function (k, v) {
							menuLinkTargetOptions += '<option value="' + k + '">' + v + '</option>';
						});

						$('<select name="' + name + '[target]" class="systemLinkTarget linkFields" style="display:block">' + menuLinkTargetOptions + '</select>')
							.insertAfter(this);
					}
				});

				$('.systemLinkApp').live('change', function () {
					var name = $(this).parent().find('.systemLinkType').attr('name');
					name = name.replace('[type]', '');

					var options = '<option value="none">-- Page --</option>';
					if ($(this).val().indexOf('/') > -1){
						var extInfo = $(this).val().split('/');

						var extension = extInfo[0];
						var application = extInfo[1];
						$.each(appJson.ext[extension][application], function (pageName, tORf) {
							options = options + '<option value="' + pageName + '">' + pageName + '</option>';
						});
					}
					else {
						$.each(appJson[$(this).val()], function (pageName, tORf) {
							options = options + '<option value="' + pageName + '">' + pageName + '</option>';
						});
					}
					$(this).parent().find('.systemLinkAppPage').remove();
					$('<select name="' + name + '[app][page]" class="systemLinkAppPage linkFields" style="display:block"></select>')
						.append(options).appendTo($(this).parent());
				});

				$.fn.reverse = [].reverse;
				$('.systemLinkCategory').live('change', function () {
					var name = $(this).parent().find('.systemLinkType').attr('name');
					name = name.replace('[type]', '');

					$(this).nextAll('.systemLinkCategory').remove();
					if ($(this).find('option:selected').attr('data-has_child') == 'true'){
						var baseArr = catJson[0];

						$(this).prevAll('.systemLinkCategory').reverse().each(function () {
							if (baseArr['children'] && baseArr['children'][$(this).val()]){
								baseArr = baseArr['children'][$(this).val()];
							}
							else {
								baseArr = baseArr[$(this).val()];
							}
						});

						if (baseArr['children'] && baseArr['children'][$(this).val()]){
							baseArr = baseArr['children'][$(this).val()];
						}
						else {
							baseArr = baseArr[$(this).val()];
						}

						if (baseArr.children){
							var options = '<option value="none">-- Category --</option>';
							$.each(baseArr.children, function (categoryId, cInfo) {
								options = options + '<option value="' + categoryId + '" data-has_child="' + (cInfo.children ? 'true' : 'false') + '">' + cInfo.name + '</option>';
							});
							var field = '<select name="' + name + '[category][id][]" class="systemLinkCategory linkFields" style="display:block"></select>';
							$(field).append(options).appendTo($(this).parent());
						}
					}
				});
			});
		</script>
		<?php
			$javascript = ob_get_contents();
			ob_end_clean();
			$App->addJavascript(__CLASS__, $javascript);
		}

		$baseInputName = $this->element->attr('name');
		$this->element->attr('name', $baseInputName . '[type]');
		$output = $this->element->draw();
		//echo '<pre>';print_r($this->settings['data']);
		if ($this->settings['data'] !== false){
			$data = $this->settings['data'];
			if (in_array($data->type, array('app', 'category', 'custom'))){
				$linkTargetMenu = htmlBase::newElement('selectbox')
					->css('display', 'block')
					->addClass('systemLinkTarget linkFields')
					->setName($baseInputName . '[target]')
					->selectOptionByValue($data->target);
				foreach($linkTargets as $k => $v){
					$linkTargetMenu->addOption($k, $v);
				}
				$output .= $linkTargetMenu->draw();

				if ($data->type == 'app'){
					$appMenu = htmlBase::newElement('selectbox')
						->setName($baseInputName . '[app][name]')
						->addClass('systemLinkApp linkFields')
						->css('display', 'block')
						->selectOptionByValue($data->app->name);
					$appMenu->addOption('none', '-- Application --');

					foreach($appArr as $appName => $pages){
						if ($appName == 'ext'){
							continue;
						}

						$appMenu->addOption($appName, $appName);
					}

					foreach($appArr['ext'] as $extName => $apps){
						foreach($apps as $appName => $pages){
							$appMenu->addOption($extName . '/' . $appName, $extName . ' > ' . $appName);
						}
					}

					$pagesMenu = htmlBase::newElement('selectbox')
						->setName($baseInputName . '[app][page]')
						->addClass('systemLinkAppPage linkFields')
						->css('display', 'block')
						->selectOptionByValue($data->app->page);
					$pagesMenu->addOption('none', '-- Page --');

					if (stristr($data->app->page, '/')){
						$extInfo = explode('/', $data->app->page);

						$extName = $extInfo[0];
						$appName = $extInfo[1];
						foreach($appArr['ext'][$extName][$appName] as $pageName => $tORf){
							$pageName = str_replace('.php', '', $pageName);
							$pagesMenu->addOption($pageName, $pageName);
						}
					}
					else {
						if (isset($appArr[$data->app->name])){
							foreach($appArr[$data->app->name] as $pageName => $tORf){
								$pageName = str_replace('.php', '', $pageName);
								$pagesMenu->addOption($pageName, $pageName);
							}
						}
					}

					$output .= $appMenu->draw() . $pagesMenu->draw();
				}elseif ($data->type == 'category'){
					$catMenus = '';
					$useCatArr = $catArr[0]['children'];
					if (isset($data->category->id)){
						foreach($data->category->id as $cID){
							$menu = htmlBase::newElement('selectbox')
								->setName($baseInputName . '[category][id][]')
								->addClass('systemLinkCategory linkFields')
								->css('display', 'block')
								->selectOptionByValue($cID);
							$menu->addOption('none', '-- Category --');

							foreach($useCatArr as $id => $cInfo){
								$menu->addOption($id, $cInfo['name'], false, array(
									'data-has_child' => (isset($cInfo['children']) && sizeof($cInfo['children']) > 0 ? 'true' : 'false')
								));
							}
							$output .= $menu->draw();

							if (isset($useCatArr[$cID])){
								$useCatArr = $useCatArr[$cID]['children'];
							}else{
								break;
							}
						}
					}
				}elseif ($data->type == 'custom'){
					$output .= htmlBase::newElement('input')
						->setName($baseInputName . '[url]')
						->addClass('linkFields')
						->css('display', 'block')
						->val($data->url)
						->draw();
				}
			}
		}
		return $output;
	}

	public function buildPrefilledBoxes(){
		return $boxes;
	}
}

?>