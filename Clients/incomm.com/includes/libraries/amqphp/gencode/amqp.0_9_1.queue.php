<?php
namespace amqp_091\protocol\queue;
/** Ampq binding code, generated from doc version 0.9.1 */
class QueueClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'queue';
    protected $index = 50;
    protected $fields = array();
    protected $methods = array(10 => 'declare',11 => 'declare-ok',20 => 'bind',21 => 'bind-ok',50 => 'unbind',51 => 'unbind-ok',30 => 'purge',31 => 'purge-ok',40 => 'delete',41 => 'delete-ok');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'declare', '\\amqp_091\\protocol\\queue\\DeclareMethod'),array(11, 'declare-ok', '\\amqp_091\\protocol\\queue\\DeclareOkMethod'),array(20, 'bind', '\\amqp_091\\protocol\\queue\\BindMethod'),array(21, 'bind-ok', '\\amqp_091\\protocol\\queue\\BindOkMethod'),array(50, 'unbind', '\\amqp_091\\protocol\\queue\\UnbindMethod'),array(51, 'unbind-ok', '\\amqp_091\\protocol\\queue\\UnbindOkMethod'),array(30, 'purge', '\\amqp_091\\protocol\\queue\\PurgeMethod'),array(31, 'purge-ok', '\\amqp_091\\protocol\\queue\\PurgeOkMethod'),array(40, 'delete', '\\amqp_091\\protocol\\queue\\DeleteMethod'),array(41, 'delete-ok', '\\amqp_091\\protocol\\queue\\DeleteOkMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array(array('reserved-1', 'declare', '\\amqp_091\\protocol\\queue\\DeclareReserved1Field'),array('queue', 'declare', '\\amqp_091\\protocol\\queue\\DeclareQueueField'),array('passive', 'declare', '\\amqp_091\\protocol\\queue\\DeclarePassiveField'),array('durable', 'declare', '\\amqp_091\\protocol\\queue\\DeclareDurableField'),array('exclusive', 'declare', '\\amqp_091\\protocol\\queue\\DeclareExclusiveField'),array('auto-delete', 'declare', '\\amqp_091\\protocol\\queue\\DeclareAutoDeleteField'),array('no-wait', 'declare', '\\amqp_091\\protocol\\queue\\DeclareNoWaitField'),array('arguments', 'declare', '\\amqp_091\\protocol\\queue\\DeclareArgumentsField'),array('queue', 'declare-ok', '\\amqp_091\\protocol\\queue\\DeclareOkQueueField'),array('message-count', 'declare-ok', '\\amqp_091\\protocol\\queue\\DeclareOkMessageCountField'),array('consumer-count', 'declare-ok', '\\amqp_091\\protocol\\queue\\DeclareOkConsumerCountField'),array('reserved-1', 'bind', '\\amqp_091\\protocol\\queue\\BindReserved1Field'),array('queue', 'bind', '\\amqp_091\\protocol\\queue\\BindQueueField'),array('exchange', 'bind', '\\amqp_091\\protocol\\queue\\BindExchangeField'),array('routing-key', 'bind', '\\amqp_091\\protocol\\queue\\BindRoutingKeyField'),array('no-wait', 'bind', '\\amqp_091\\protocol\\queue\\BindNoWaitField'),array('arguments', 'bind', '\\amqp_091\\protocol\\queue\\BindArgumentsField'),array('reserved-1', 'unbind', '\\amqp_091\\protocol\\queue\\UnbindReserved1Field'),array('queue', 'unbind', '\\amqp_091\\protocol\\queue\\UnbindQueueField'),array('exchange', 'unbind', '\\amqp_091\\protocol\\queue\\UnbindExchangeField'),array('routing-key', 'unbind', '\\amqp_091\\protocol\\queue\\UnbindRoutingKeyField'),array('arguments', 'unbind', '\\amqp_091\\protocol\\queue\\UnbindArgumentsField'),array('reserved-1', 'purge', '\\amqp_091\\protocol\\queue\\PurgeReserved1Field'),array('queue', 'purge', '\\amqp_091\\protocol\\queue\\PurgeQueueField'),array('no-wait', 'purge', '\\amqp_091\\protocol\\queue\\PurgeNoWaitField'),array('message-count', 'purge-ok', '\\amqp_091\\protocol\\queue\\PurgeOkMessageCountField'),array('reserved-1', 'delete', '\\amqp_091\\protocol\\queue\\DeleteReserved1Field'),array('queue', 'delete', '\\amqp_091\\protocol\\queue\\DeleteQueueField'),array('if-unused', 'delete', '\\amqp_091\\protocol\\queue\\DeleteIfUnusedField'),array('if-empty', 'delete', '\\amqp_091\\protocol\\queue\\DeleteIfEmptyField'),array('no-wait', 'delete', '\\amqp_091\\protocol\\queue\\DeleteNoWaitField'),array('message-count', 'delete-ok', '\\amqp_091\\protocol\\queue\\DeleteOkMessageCountField'));
}



class DeclareMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'declare';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('declare-ok');
    protected $fields = array('reserved-1', 'queue', 'passive', 'durable', 'exclusive', 'auto-delete', 'no-wait', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class DeclareOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'declare-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('queue', 'message-count', 'consumer-count');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class BindMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'bind';
    protected $index = 20;
    protected $synchronous = true;
    protected $responseMethods = array('bind-ok');
    protected $fields = array('reserved-1', 'queue', 'exchange', 'routing-key', 'no-wait', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class BindOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'bind-ok';
    protected $index = 21;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class UnbindMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'unbind';
    protected $index = 50;
    protected $synchronous = true;
    protected $responseMethods = array('unbind-ok');
    protected $fields = array('reserved-1', 'queue', 'exchange', 'routing-key', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class UnbindOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'unbind-ok';
    protected $index = 51;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class PurgeMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'purge';
    protected $index = 30;
    protected $synchronous = true;
    protected $responseMethods = array('purge-ok');
    protected $fields = array('reserved-1', 'queue', 'no-wait');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class PurgeOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'purge-ok';
    protected $index = 31;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('message-count');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class DeleteMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'delete';
    protected $index = 40;
    protected $synchronous = true;
    protected $responseMethods = array('delete-ok');
    protected $fields = array('reserved-1', 'queue', 'if-unused', 'if-empty', 'no-wait');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class DeleteOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'queue';
    protected $name = 'delete-ok';
    protected $index = 41;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('message-count');
    protected $methFact = '\\amqp_091\\protocol\\queue\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\queue\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class DeclareReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class DeclareQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

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

  
class DeclareExclusiveField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exclusive'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeclareAutoDeleteField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'auto-delete'; }
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

  
class DeclareOkQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class DeclareOkMessageCountField extends \amqp_091\protocol\MessageCountDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'message-count'; }
    function getSpecFieldDomain() { return 'message-count'; }

}

  
class DeclareOkConsumerCountField extends \amqp_091\protocol\LongDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'consumer-count'; }
    function getSpecFieldDomain() { return 'long'; }

}

  
class BindReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class BindQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

}

  
class BindExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
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

  
class UnbindQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

}

  
class UnbindExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class UnbindRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class UnbindArgumentsField extends \amqp_091\protocol\TableDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'arguments'; }
    function getSpecFieldDomain() { return 'table'; }

}

  
class PurgeReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class PurgeQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

}

  
class PurgeNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class PurgeOkMessageCountField extends \amqp_091\protocol\MessageCountDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'message-count'; }
    function getSpecFieldDomain() { return 'message-count'; }

}

  
class DeleteReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class DeleteQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

}

  
class DeleteIfUnusedField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'if-unused'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeleteIfEmptyField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'if-empty'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class DeleteNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class DeleteOkMessageCountField extends \amqp_091\protocol\MessageCountDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'message-count'; }
    function getSpecFieldDomain() { return 'message-count'; }

}

  