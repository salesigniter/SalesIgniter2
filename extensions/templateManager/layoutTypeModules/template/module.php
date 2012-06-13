<?php
class TemplateManagerLayoutTypeModuleTemplate extends TemplateManagerLayoutTypeModuleBase
{

	protected $title = 'Page Template';

	protected $code = 'template';

	protected $startingLayoutPath = 'extensions/templateManager/layoutTypeModules/template/startingLayout/';

	public function getSetWidth(){
		return 960;
	}

	public function getLayoutSettings($Layout)
	{
		global $App;

		$selApps = array();
		$AppArray = $App->getApplications($selApps, false);

		$QselApps = Doctrine_Query::create()
			->from('TemplatePages')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		foreach($QselApps as $sInfo){
			$layouts = explode(',', $sInfo['layout_id']);
			$pageType = explode(',', $sInfo['page_type']);
			$assocurls = explode(',', $sInfo['associative_url']);
			if (in_array($Layout->layout_id, $layouts)){
				if (!empty($sInfo['extension'])){
					$selApps['ext'][$sInfo['extension']][$sInfo['application']][$sInfo['page']] = true;
					$pageTypes['ext'][$sInfo['extension']][$sInfo['application']][$sInfo['page']] = $pageType[array_search($Layout->layout_id,$layouts)];
					$assocurl['ext'][$sInfo['extension']][$sInfo['application']][$sInfo['page']] = $assocurls[array_search($Layout->layout_id,$layouts)];

				}
				else {
					$selApps[$sInfo['application']][$sInfo['page']] = true;
					$pageTypes[$sInfo['application']][$sInfo['page']] = $pageType[array_search($Layout->layout_id,$layouts)];
					$assocurl[$sInfo['application']][$sInfo['page']] = $assocurls[array_search($Layout->layout_id,$layouts)];
				}
			}
		}

		$BoxesContainer = htmlBase::newElement('div');

		$rentalMemberCheckbox = htmlBase::newElement('checkbox')->setLabel('R')->setValue('R');
		$nonRentalMemberCheckbox = htmlBase::newElement('checkbox')->setLabel('N')->setValue('N');

		$col = 0;
		foreach($AppArray as $appName => $aInfo){
			if ($appName == 'ext'){
				continue;
			}

			if (!empty($aInfo)){
				$Box = htmlBase::newElement('div')
					->addClass('ui-widget-content ui-corner-all mainBox')
					->css(array(
					'float' => 'left',
					'margin' => '.5em',
					'min-width' => '260px',
					'min-height' => '250px',
					'padding' => '.5em'
				));

				$checkboxes = '<div class="ui-widget-header"><input type="checkbox" class="appBox checkAllPages"> ' . $appName . '</div>';
				foreach($aInfo as $pageName => $pageChecked){
					$pageName1 = $pageName;
					if($appName == 'product' && is_numeric($pageName) && isset($associativeUrl)){
						$QProducts = Doctrine_Query::create()
							->from('Products p')
							->leftJoin('p.ProductsDescription pd')
							->where('pd.language_id = ?', Session::get('languages_id'))
							->andWhere('p.products_id = ?', $pageName)
							->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

						$pageName1 = $QProducts[0]['ProductsDescription'][0]['products_name'];
					}
					$rentalMemberCheckbox
						->setName('pagetype[' . $appName . '][' . $pageName . ']')
						->setChecked((isset($pageTypes[$appName][$pageName]) && $pageTypes[$appName][$pageName] == 'R') ? true : false);

					$nonRentalMemberCheckbox
						->setName('pagetype[' . $appName . '][' . $pageName . ']')
						->setChecked((isset($pageTypes[$appName][$pageName]) && $pageTypes[$appName][$pageName] == 'N') ? true : false);

					$checkboxes .= '<div style="margin: 0 0 0 1em;"><input class="pageBox" type="checkbox" name="applications[' . $appName . '][]" value="' . $pageName . '"' . ($pageChecked === true ? ' checked="checked"' : '') . '> ' . $pageName1;
					$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$nonRentalMemberCheckbox->draw();
					$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$rentalMemberCheckbox->draw();
					if(isset($associativeUrl)){
						$associativeUrl->setName('assocurl['. $appName . '][' . $pageName . ']')
							->setValue(isset($assocurl[$appName][$pageName])?$assocurl[$appName][$pageName]:'');
						$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$associativeUrl->draw();
					}
					$checkboxes .= '</div>';

				}

				$Box->html($checkboxes);
				$BoxesContainer->append($Box);
			}
		}

		foreach($AppArray['ext'] as $ExtName => $eInfo){
			if (!empty($eInfo)){
				$Box = htmlBase::newElement('div')
					->addClass('ui-widget-content ui-corner-all mainBox')
					->css(array(
					'float' => 'left',
					'margin' => '.5em',
					'min-width' => '260px',
					'min-height' => '250px',
					'padding' => '.5em'
				));

				$checkboxes = '<div class="ui-widget-header"><input type="checkbox" class="extensionBox checkAllApps"> ' . $ExtName . '</div>';
				foreach($eInfo as $appName => $aInfo){
					$checkboxes .= '<div><div class="ui-state-hover" style="margin: .5em .5em 0 .5em"><input type="checkbox" class="appBox checkAllPages"> ' . $appName . '</div>';
					foreach($aInfo as $pageName => $pageChecked){
						$rentalMemberCheckbox
							->setName('pagetype[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
							->setChecked((isset($pageTypes['ext'][$ExtName][$appName][$pageName]) && $pageTypes['ext'][$ExtName][$appName][$pageName] == 'R') ? true : false);

						$nonRentalMemberCheckbox
							->setName('pagetype[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
							->setChecked((isset($pageTypes['ext'][$ExtName][$appName][$pageName]) && $pageTypes['ext'][$ExtName][$appName][$pageName] == 'N') ? true : false);

						$checkboxes .= '<div style="margin: 0 0 0 1em;"><input type="checkbox" class="pageBox" name="applications[ext][' . $ExtName . '][' . $appName . '][]" value="' . $pageName . '"' . ($pageChecked === true ? ' checked="checked"' : '') . '> ' . $pageName;
						$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$nonRentalMemberCheckbox->draw();
						$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$rentalMemberCheckbox->draw();

						if(isset($associativeUrl)){
							$associativeUrl->setName('assocurl[ext][' . $ExtName . '][' . $appName . '][' . $pageName . ']')
								->setValue(isset($assocurl['ext'][$ExtName][$appName][$pageName])?$assocurl['ext'][$ExtName][$appName][$pageName]:'');
							$checkboxes .= '&nbsp;&nbsp;&nbsp;'.$associativeUrl->draw();
						}
						$checkboxes .= '</div>';
					}
					$checkboxes .= '</div>';
				}

				$Box->html($checkboxes);
				$BoxesContainer->append($Box);
			}
		}
		$BoxesContainer->append(htmlBase::newElement('div')->addClass('ui-helper-clearfix'));

		$SettingsTable = htmlBase::newElement('table');

		if ($Layout->layout_id <= 0){
			$SettingsTable->addBodyRow(array(
				'columns' => array(
					array('text' => 'Select starting layout:'),
					array('text' => $this->getLayoutTypeSelect())
				)
			));
		}

		$SettingsTable->addBodyRow(array(
			'columns' => array(
				array('text' => 'Display Type:'),
				array(
					'text' => htmlBase::newElement('selectbox')
						->setName('layoutType')
						->addOption('desktop', 'Desktop')
						->addOption('smartphone', 'Smart Phone')
						->addOption('tablet', 'Tablet')
						->selectOptionByValue($Layout->layout_type)
						->draw()
				)
			)
		));

		$SettingsTable->addBodyRow(array(
			'attr'    => array(
				'data-for_page_type' => 'template'
			),
			'columns' => array(
				array('text' => 'Layout Pages:'),
				array('css'=> array('color'=> 'red'), 'text' => '<strong>N : Non Rental Members <br> R : Rental Members</strong>')
			)
		));

		$SettingsTable->addBodyRow(array(
			'attr'    => array(
				'data-for_page_type' => 'template'
			),
			'columns' => array(
				array('colspan' => 2, 'text' => '<input type="checkbox" class="checkAll"/> <span class="checkAllText">Check All</span>' . $BoxesContainer->draw())
			)
		));

		ob_start();
		echo $SettingsTable->draw();

		?>
	<script>
		var height = 0;
		var width = 0;
		$('.mainBox').each(function () {
			if ($(this).outerWidth() > width){
				width = $(this).outerWidth();
			}

			if ($(this).outerHeight() > height){
				height = $(this).outerHeight();
			}
		});

		$('.mainBox').width(width).height(height);

		$('.checkAll').click(function () {
			var self = this;
			$(this).parent().find('input:checkbox').each(function () {
				this.checked = self.checked;
			});

			if (self.checked){
				$(this).parent().find('.checkAllText').html('Uncheck All');
			}
			else {
				$(this).parent().find('.checkAllText').html('Check All');
			}
		});

		$('.checkAllPages').click(function () {
			var self = this;
			$(self).parent().parent().find('.pageBox').each(function () {
				this.checked = self.checked;
			});
		});

		$('.checkAllApps').click(function () {
			var self = this;
			$(self).parent().parent().find('.appBox').each(function () {
				this.checked = self.checked;
			});
			$(self).parent().parent().find('.pageBox').each(function () {
				this.checked = self.checked;
			});
		});
	</script>
	<?php
		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	}

	public function onSave($Layout){
		$TemplatePages = Doctrine_Core::getTable('TemplatePages');
		$Reset = $TemplatePages->findAll();

		foreach($Reset as $rInfo){
			$layouts = explode(',', $rInfo->layout_id);
			if (in_array($Layout->layout_id, $layouts)){
				foreach($layouts as $idx => $id){
					if ($id == $Layout->layout_id){
						unset($layouts[$idx]);
					}

					if ($id == ''){
						unset($layouts[$idx]);
					}
				}
				$rInfo->layout_id = implode(',', $layouts);
				$rInfo->save();
			}
		}

		if (isset($_POST['applications'])){
			foreach($_POST['applications'] as $appName => $Pages){
				if ($appName == 'ext'){
					continue;
				}

				foreach($Pages as $pageName){
					$TemplatePage = $TemplatePages->findOneByApplicationAndPage($appName, $pageName);
					$pageType = !empty($_POST['pagetype'][$appName][$pageName]) ? $_POST['pagetype'][$appName][$pageName] : '';
					$assocurl = !empty($_POST['assocurl'][$appName][$pageName]) ? $_POST['assocurl'][$appName][$pageName] : '';
					$currentPageTypesNew = false;
					$currentAssocNew = false;

					if (!$TemplatePage){
						$TemplatePage = new TemplatePages();
						$TemplatePage->application = $appName;
						$TemplatePage->page = $pageName;
					}

					$currentLayouts = explode(',', $TemplatePage->layout_id);
					$currentPageTypes = explode(',', $TemplatePage->page_type);
					$currentAssoc = explode(',', $TemplatePage->associative_url);

					foreach($currentLayouts as $key => $currentLayout){
						//echo 'inside ' . $Layout->layout_id . ' = ' . $currentLayout . "\n";
						if ($Layout->layout_id == $currentLayout){
							$currentPageTypesNew[$key] = $pageType;
							$currentAssocNew[$key] = $assocurl;
						}
						else {
							if (isset($currentPageTypes[$key])){
								$currentPageTypesNew[$key] = $currentPageTypes[$key];
							}
							else {
								$currentPageTypesNew[$key] = '';
							}

							if (isset($currentAssoc[$key])){
								$currentAssocNew[$key] = $currentAssoc[$key];
							}
							else {
								$currentAssocNew[$key] = '';
							}
						}
					}

					if (!in_array($Layout->layout_id, $currentLayouts)){
						$currentLayouts[] = $Layout->layout_id;
						$currentPageTypesNew[] = $pageType;
						$currentAssocNew[] = $assocurl;
					}

					$TemplatePage->page_type = implode(',', $currentPageTypesNew);
					$TemplatePage->associative_url = implode(',', $currentAssocNew);
					$TemplatePage->layout_id = implode(',', $currentLayouts);
					$TemplatePage->save();
				}
			}
		}

		if (isset($_POST['applications']['ext'])){
			foreach($_POST['applications']['ext'] as $extName => $Applications){
				foreach($Applications as $appName => $Pages){
					foreach($Pages as $pageName){
						$TemplatePage = $TemplatePages->findOneByApplicationAndPageAndExtension($appName, $pageName, $extName);
						$pageType = !empty($_POST['pagetype']['ext'][$extName][$pageName]) ? $_POST['pagetype']['ext'][$extName][$pageName] : '';
						$currentPageTypesNew = false;
						$assocurl = !empty($_POST['assocurl']['ext'][$extName][$pageName]) ? $_POST['assocurl']['ext'][$extName][$pageName] : '';
						$currentAssocNew = false;

						if (!$TemplatePage){
							$TemplatePage = new TemplatePages();
							$TemplatePage->application = $appName;
							$TemplatePage->page = $pageName;
							$TemplatePage->extension = $extName;
						}

						$currentLayouts = explode(',', $TemplatePage->layout_id);
						$currentPageTypes = explode(',', $TemplatePage->page_type);
						$currentAssoc = explode(',', $TemplatePage->associative_url);

						foreach($currentLayouts as $key=> $currentLayout){
							if ($Layout->layout_id == $currentLayout){
								$currentPageTypesNew[$key] = $pageType;
								$currentAssocNew[$key] = $assocurl;
							}
							else {
								if (isset($currentPageTypes[$key])){
									$currentPageTypesNew[$key] = $currentPageTypes[$key];
								}
								else {
									$currentPageTypesNew[$key] = '';
								}
								if (isset($currentAssoc[$key])){
									$currentAssocNew[$key] = $currentAssoc[$key];
								}
								else {
									$currentAssocNew[$key] = '';
								}
							}
						}

						if (!in_array($Layout->layout_id, $currentLayouts)){
							$currentLayouts[] = $Layout->layout_id;
							$currentPageTypesNew[] = $pageType;
							$currentAssocNew[] = $assocurl;
						}
						$TemplatePage->page_type = implode(',', $currentPageTypesNew);
						$TemplatePage->associative_url = implode(',', $currentAssocNew);
						$TemplatePage->layout_id = implode(',', $currentLayouts);
						$TemplatePage->save();
					}
				}
			}
		}
	}
}
