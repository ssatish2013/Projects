<?php
namespace amqp_091\protocol\confirm;
/** Ampq binding code, generated from doc version 0.9.1 */
class ConfirmClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'confirm';
    protected $index = 85;
    protected $fields = array();
    protected $methods = array(10 => 'select',11 => 'select-ok');
    protected $methFact = '\\amqp_091\\protocol\\confirm\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\confirm\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'select', '\\amqp_091\\protocol\\confirm\\SelectMethod'),array(11, 'select-ok', '\\amqp_091\\protocol\\confirm\\SelectOkMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array(array('nowait', 'select', '\\amqp_091\\protocol\\confirm\\SelectNowaitField'));
}



class SelectMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'confirm';
    protected $name = 'select';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('select-ok');
    protected $fields = array('nowait');
    protected $methFact = '\\amqp_091\\protocol\\confirm\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\confirm\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class SelectOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'confirm';
    protected $name = 'select-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\confirm\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\confirm\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class SelectNowaitField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'nowait'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  