<?php

/**
 * TemplateManagerLayoutsColumns
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @property integer $container_id
 * @property integer $column_id
 * @property integer $sort_order
 * @property TemplateManagerLayoutsContainers $TemplateManagerLayoutsContainers
 * @property Doctrine_Collection $Widgets
 * @property Doctrine_Collection $Configuration
 * @property Doctrine_Collection $Styles
 *
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class TemplateManagerLayoutsColumns extends Doctrine_Record
{
	public function setTableDefinition()
	{
		$this->setTableName('template_manager_layouts_columns');
		$this->hasColumn('container_id', 'integer', 11, array(
			'type' => 'integer',
			'length' => '11',
		));
		$this->hasColumn('column_id', 'integer', 11, array(
			'primary' => true,
			'type' => 'integer',
			'autoincrement' => true,
			'length' => '11',
		));
		$this->hasColumn('sort_order', 'integer', 3, array(
			'type' => 'integer',
			'length' => '3',
		));
	}

	public function setUp()
	{
		parent::setUp();
		$this->hasOne('TemplateManagerLayoutsContainers as Container', array(
			'local' => 'container_id',
			'foreign' => 'container_id'));

		$this->hasMany('TemplateManagerLayoutsWidgets as Widgets', array(
			'local' => 'column_id',
			'foreign' => 'column_id',
			'orderBy' => 'sort_order',
			'cascade' => array('delete')));

		$this->hasMany('TemplateManagerLayoutsColumnsConfiguration as Configuration', array(
			'local' => 'column_id',
			'foreign' => 'column_id',
			'cascade' => array('delete')));

		$this->hasMany('TemplateManagerLayoutsColumnsStyles as Styles', array(
			'local' => 'column_id',
			'foreign' => 'column_id',
			'cascade' => array('delete')));
	}
}