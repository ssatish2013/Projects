<?php
namespace amqp_091\protocol\exchange;
/** Ampq binding code, generated from doc version 0.9.1 */
class ExchangeClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'exchange';
    protected $index = 40;
    protected $fields = array();
    protected $methods = array(10 => 'declare',11 => 'declare-ok',20 => 'delete',21 => 'delete-ok',30 => 'bind',31 => 'bind-ok',40 => 'unbind',41 => 'unbind-ok');
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'declare', '\\amqp_091\\protocol\\exchange\\DeclareMethod'),array(11, 'declare-ok', '\\amqp_091\\protocol\\exchange\\DeclareOkMethod'),array(20, 'delete', '\\amqp_091\\protocol\\exchange\\DeleteMethod'),array(21, 'delete-ok', '\\amqp_091\\protocol\\exchange\\DeleteOkMethod'),array(30, 'bind', '\\amqp_091\\protocol\\exchange\\BindMethod'),array(31, 'bind-ok', '\\amqp_091\\protocol\\exchange\\BindOkMethod'),array(40, 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindMethod'),array(41, 'unbind-ok', '\\amqp_091\\protocol\\exchange\\UnbindOkMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array(array('reserved-1', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareReserved1Field'),array('exchange', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareExchangeField'),array('type', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareTypeField'),array('passive', 'declare', '\\amqp_091\\protocol\\exchange\\DeclarePassiveField'),array('durable', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareDurableField'),array('reserved-2', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareReserved2Field'),array('reserved-3', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareReserved3Field'),array('no-wait', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareNoWaitField'),array('arguments', 'declare', '\\amqp_091\\protocol\\exchange\\DeclareArgumentsField'),array('reserved-1', 'delete', '\\amqp_091\\protocol\\exchange\\DeleteReserved1Field'),array('exchange', 'delete', '\\amqp_091\\protocol\\exchange\\DeleteExchangeField'),array('if-unused', 'delete', '\\amqp_091\\protocol\\exchange\\DeleteIfUnusedField'),array('no-wait', 'delete', '\\amqp_091\\protocol\\exchange\\DeleteNoWaitField'),array('reserved-1', 'bind', '\\amqp_091\\protocol\\exchange\\BindReserved1Field'),array('destination', 'bind', '\\amqp_091\\protocol\\exchange\\BindDestinationField'),array('source', 'bind', '\\amqp_091\\protocol\\exchange\\BindSourceField'),array('routing-key', 'bind', '\\amqp_091\\protocol\\exchange\\BindRoutingKeyField'),array('no-wait', 'bind', '\\amqp_091\\protocol\\exchange\\BindNoWaitField'),array('arguments', 'bind', '\\amqp_091\\protocol\\exchange\\BindArgumentsField'),array('reserved-1', 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindReserved1Field'),array('destination', 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindDestinationField'),array('source', 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindSourceField'),array('routing-key', 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindRoutingKeyField'),array('no-wait', 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindNoWaitField'),array('arguments', 'unbind', '\\amqp_091\\protocol\\exchange\\UnbindArgumentsField'));
}



class DeclareMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'declare';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('declare-ok');
    protected $fields = array('reserved-1', 'exchange', 'type', 'passive', 'durable', 'reserved-2', 'reserved-3', 'no-wait', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class DeclareOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'declare-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class DeleteMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'delete';
    protected $index = 20;
    protected $synchronous = true;
    protected $responseMethods = array('delete-ok');
    protected $fields = array('reserved-1', 'exchange', 'if-unused', 'no-wait');
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class DeleteOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'delete-ok';
    protected $index = 21;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class BindMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'bind';
    protected $index = 30;
    protected $synchronous = true;
    protected $responseMethods = array('bind-ok');
    protected $fields = array('reserved-1', 'destination', 'source', 'routing-key', 'no-wait', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class BindOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'bind-ok';
    protected $index = 31;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class UnbindMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'unbind';
    protected $index = 40;
    protected $synchronous = true;
    protected $responseMethods = array('unbind-ok');
    protected $fields = array('reserved-1', 'destination', 'source', 'routing-key', 'no-wait', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class UnbindOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'exchange';
    protected $name = 'unbind-ok';
    protected $index = 41;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\exchange\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\exchange\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class DeclareReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class DeclareExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class DeclareTypeField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'type'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class DeclarePassiveField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'passive'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeclareDurableField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'durable'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeclareReserved2Field extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-2'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeclareReserved3Field extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-3'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeclareNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class DeclareArgumentsField extends \amqp_091\protocol\TableDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'arguments'; }
    function getSpecFieldDomain() { return 'table'; }

}

  
class DeleteReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class DeleteExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class DeleteIfUnusedField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'if-unused'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeleteNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class BindReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class BindDestinationField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'destination'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class BindSourceField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'source'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class BindRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class BindNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class BindArgumentsField extends \amqp_091\protocol\TableDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'arguments'; }
    function getSpecFieldDomain() { return 'table'; }

}

  
class UnbindReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class UnbindDestinationField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'destination'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class UnbindSourceField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'source'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class UnbindRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class UnbindNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class UnbindArgumentsField extends \amqp_091\protocol\TableDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'arguments'; }
    function getSpecFieldDomain() { return 'table'; }

}

  