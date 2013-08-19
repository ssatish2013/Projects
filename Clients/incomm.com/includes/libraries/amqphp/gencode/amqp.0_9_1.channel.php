<?php
namespace amqp_091\protocol\channel;
/** Ampq binding code, generated from doc version 0.9.1 */
class ChannelClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'channel';
    protected $index = 20;
    protected $fields = array();
    protected $methods = array(10 => 'open',11 => 'open-ok',20 => 'flow',21 => 'flow-ok',40 => 'close',41 => 'close-ok');
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'open', '\\amqp_091\\protocol\\channel\\OpenMethod'),array(11, 'open-ok', '\\amqp_091\\protocol\\channel\\OpenOkMethod'),array(20, 'flow', '\\amqp_091\\protocol\\channel\\FlowMethod'),array(21, 'flow-ok', '\\amqp_091\\protocol\\channel\\FlowOkMethod'),array(40, 'close', '\\amqp_091\\protocol\\channel\\CloseMethod'),array(41, 'close-ok', '\\amqp_091\\protocol\\channel\\CloseOkMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array(array('reserved-1', 'open', '\\amqp_091\\protocol\\channel\\OpenReserved1Field'),array('reserved-1', 'open-ok', '\\amqp_091\\protocol\\channel\\OpenOkReserved1Field'),array('active', 'flow', '\\amqp_091\\protocol\\channel\\FlowActiveField'),array('active', 'flow-ok', '\\amqp_091\\protocol\\channel\\FlowOkActiveField'),array('reply-code', 'close', '\\amqp_091\\protocol\\channel\\CloseReplyCodeField'),array('reply-text', 'close', '\\amqp_091\\protocol\\channel\\CloseReplyTextField'),array('class-id', 'close', '\\amqp_091\\protocol\\channel\\CloseClassIdField'),array('method-id', 'close', '\\amqp_091\\protocol\\channel\\CloseMethodIdField'));
}



class OpenMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'channel';
    protected $name = 'open';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('open-ok');
    protected $fields = array('reserved-1');
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class OpenOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'channel';
    protected $name = 'open-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('reserved-1');
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class FlowMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'channel';
    protected $name = 'flow';
    protected $index = 20;
    protected $synchronous = true;
    protected $responseMethods = array('flow-ok');
    protected $fields = array('active');
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class FlowOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'channel';
    protected $name = 'flow-ok';
    protected $index = 21;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('active');
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CloseMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'channel';
    protected $name = 'close';
    protected $index = 40;
    protected $synchronous = true;
    protected $responseMethods = array('close-ok');
    protected $fields = array('reply-code', 'reply-text', 'class-id', 'method-id');
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CloseOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'channel';
    protected $name = 'close-ok';
    protected $index = 41;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\channel\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\channel\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class OpenReserved1Field extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class OpenOkReserved1Field extends \amqp_091\protocol\LongstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'longstr'; }

}

  
class FlowActiveField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'active'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class FlowOkActiveField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'active'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class CloseReplyCodeField extends \amqp_091\protocol\ReplyCodeDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reply-code'; }
    function getSpecFieldDomain() { return 'reply-code'; }

}

  
class CloseReplyTextField extends \amqp_091\protocol\ReplyTextDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reply-text'; }
    function getSpecFieldDomain() { return 'reply-text'; }

}

  
class CloseClassIdField extends \amqp_091\protocol\ClassIdDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'class-id'; }
    function getSpecFieldDomain() { return 'class-id'; }

}

  
class CloseMethodIdField extends \amqp_091\protocol\MethodIdDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'method-id'; }
    function getSpecFieldDomain() { return 'method-id'; }

}

  