<?php
/**
 * Image Resizer Widget Class
 * @package Html
 */
class htmlWidget_imageResizer extends htmlWidget
{

	protected $settings = array();

	public function __construct($options = array()) {
		$this->settings = array_merge(array(
			'data' => json_decode(json_encode(array(
				'width' => '',
				'height' => ''
			)))
		), $options);

		$this->element = htmlBase::newElement('div');
	}

	public function draw() {
		global $App;
		$baseInputName = $this->element->attr('name');
		$WidthElement = htmlBase::newElement('input')
			->addClass('imageWidth')
			->attr('size', 5)
			->setName($baseInputName . '[dimensions][width]')
			->val((isset($this->settings['data']->width) ? $this->settings['data']->width : ''));

		$HeightElement = htmlBase::newElement('input')
			->addClass('imageHeight')
			->attr('size', 5)
			->setName($baseInputName . '[dimensions][height]')
			->val((isset($this->settings['data']->height) ? $this->settings['data']->height : ''));

		$LinkElement = htmlBase::newElement('icon')
			->addClass('linkSizes')
			->setType('link');

		return '<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td>' . $WidthElement->draw() . '</td>
				<td>&nbsp;' . $LinkElement->draw() . '&nbsp;</td>
				<td>' . $HeightElement->draw() . '</td>
			</tr>
			<tr>
				<td align="center">Width</td>
				<td></td>
				<td align="center">Height</td>
			</tr>
		</table>';
	}
}

?>