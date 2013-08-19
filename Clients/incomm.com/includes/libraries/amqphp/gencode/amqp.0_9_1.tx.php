<?php
namespace amqp_091\protocol\tx;
/** Ampq binding code, generated from doc version 0.9.1 */
class TxClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'tx';
    protected $index = 90;
    protected $fields = array();
    protected $methods = array(10 => 'select',11 => 'select-ok',20 => 'commit',21 => 'commit-ok',30 => 'rollback',31 => 'rollback-ok');
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'select', '\\amqp_091\\protocol\\tx\\SelectMethod'),array(11, 'select-ok', '\\amqp_091\\protocol\\tx\\SelectOkMethod'),array(20, 'commit', '\\amqp_091\\protocol\\tx\\CommitMethod'),array(21, 'commit-ok', '\\amqp_091\\protocol\\tx\\CommitOkMethod'),array(30, 'rollback', '\\amqp_091\\protocol\\tx\\RollbackMethod'),array(31, 'rollback-ok', '\\amqp_091\\protocol\\tx\\RollbackOkMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array();
}



class SelectMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'tx';
    protected $name = 'select';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('select-ok');
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class SelectOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'tx';
    protected $name = 'select-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CommitMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'tx';
    protected $name = 'commit';
    protected $index = 20;
    protected $synchronous = true;
    protected $responseMethods = array('commit-ok');
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CommitOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'tx';
    protected $name = 'commit-ok';
    protected $index = 21;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class RollbackMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'tx';
    protected $name = 'rollback';
    protected $index = 30;
    protected $synchronous = true;
    protected $responseMethods = array('rollback-ok');
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class RollbackOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'tx';
    protected $name = 'rollback-ok';
    protected $index = 31;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\tx\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\tx\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  