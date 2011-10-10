<?php

/**
 * TemplateManagerLayoutsWidgetsConfiguration
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $widget_id
 * @property integer $configuration_id
 * @property string $configuration_key
 * @property string $configuration_value
 * @property TemplateManagerLayoutsWidgets $TemplateManagerLayoutsWidgets
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class TemplateManagerLayoutsWidgetsConfiguration extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('template_manager_layouts_widgets_configuration');
        $this->hasColumn('widget_id', 'integer', 11, array(
             'type' => 'integer',
             'length' => '11',
             ));
        $this->hasColumn('configuration_id', 'integer', 11, array(
             'primary' => true,
             'type' => 'integer',
             'autoincrement' => true,
             'length' => '11',
             ));
        $this->hasColumn('configuration_key', 'string', 128, array(
             'type' => 'string',
             'length' => '128',
             ));
        $this->hasColumn('configuration_value', 'string', null, array(
             'type' => 'string',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->setAttribute(Doctrine_Core::ATTR_COLL_KEY, 'configuration_key');
        $this->hasOne('TemplateManagerLayoutsWidgets', array(
             'local' => 'widget_id',
             'foreign' => 'widget_id'));
    }
}