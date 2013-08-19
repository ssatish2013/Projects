<?php
namespace amqp_091\protocol\basic;
/** Ampq binding code, generated from doc version 0.9.1 */
class BasicClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'basic';
    protected $index = 60;
    protected $fields = array('content-type','content-encoding','headers','delivery-mode','priority','correlation-id','reply-to','expiration','message-id','timestamp','type','user-id','app-id','cluster-id');
    protected $methods = array(10 => 'qos',11 => 'qos-ok',20 => 'consume',21 => 'consume-ok',30 => 'cancel',31 => 'cancel-ok',40 => 'publish',50 => 'return',60 => 'deliver',70 => 'get',71 => 'get-ok',72 => 'get-empty',80 => 'ack',90 => 'reject',100 => 'recover-async',110 => 'recover',111 => 'recover-ok',120 => 'nack');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'qos', '\\amqp_091\\protocol\\basic\\QosMethod'),array(11, 'qos-ok', '\\amqp_091\\protocol\\basic\\QosOkMethod'),array(20, 'consume', '\\amqp_091\\protocol\\basic\\ConsumeMethod'),array(21, 'consume-ok', '\\amqp_091\\protocol\\basic\\ConsumeOkMethod'),array(30, 'cancel', '\\amqp_091\\protocol\\basic\\CancelMethod'),array(31, 'cancel-ok', '\\amqp_091\\protocol\\basic\\CancelOkMethod'),array(40, 'publish', '\\amqp_091\\protocol\\basic\\PublishMethod'),array(50, 'return', '\\amqp_091\\protocol\\basic\\ReturnMethod'),array(60, 'deliver', '\\amqp_091\\protocol\\basic\\DeliverMethod'),array(70, 'get', '\\amqp_091\\protocol\\basic\\GetMethod'),array(71, 'get-ok', '\\amqp_091\\protocol\\basic\\GetOkMethod'),array(72, 'get-empty', '\\amqp_091\\protocol\\basic\\GetEmptyMethod'),array(80, 'ack', '\\amqp_091\\protocol\\basic\\AckMethod'),array(90, 'reject', '\\amqp_091\\protocol\\basic\\RejectMethod'),array(100, 'recover-async', '\\amqp_091\\protocol\\basic\\RecoverAsyncMethod'),array(110, 'recover', '\\amqp_091\\protocol\\basic\\RecoverMethod'),array(111, 'recover-ok', '\\amqp_091\\protocol\\basic\\RecoverOkMethod'),array(120, 'nack', '\\amqp_091\\protocol\\basic\\NackMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array(array('content-type', '', '\\amqp_091\\protocol\\basic\\ContentTypeField'),array('content-encoding', '', '\\amqp_091\\protocol\\basic\\ContentEncodingField'),array('headers', '', '\\amqp_091\\protocol\\basic\\HeadersField'),array('delivery-mode', '', '\\amqp_091\\protocol\\basic\\DeliveryModeField'),array('priority', '', '\\amqp_091\\protocol\\basic\\PriorityField'),array('correlation-id', '', '\\amqp_091\\protocol\\basic\\CorrelationIdField'),array('reply-to', '', '\\amqp_091\\protocol\\basic\\ReplyToField'),array('expiration', '', '\\amqp_091\\protocol\\basic\\ExpirationField'),array('message-id', '', '\\amqp_091\\protocol\\basic\\MessageIdField'),array('timestamp', '', '\\amqp_091\\protocol\\basic\\TimestampField'),array('type', '', '\\amqp_091\\protocol\\basic\\TypeField'),array('user-id', '', '\\amqp_091\\protocol\\basic\\UserIdField'),array('app-id', '', '\\amqp_091\\protocol\\basic\\AppIdField'),array('cluster-id', '', '\\amqp_091\\protocol\\basic\\ClusterIdField'),array('prefetch-size', 'qos', '\\amqp_091\\protocol\\basic\\QosPrefetchSizeField'),array('prefetch-count', 'qos', '\\amqp_091\\protocol\\basic\\QosPrefetchCountField'),array('global', 'qos', '\\amqp_091\\protocol\\basic\\QosGlobalField'),array('reserved-1', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeReserved1Field'),array('queue', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeQueueField'),array('consumer-tag', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeConsumerTagField'),array('no-local', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeNoLocalField'),array('no-ack', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeNoAckField'),array('exclusive', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeExclusiveField'),array('no-wait', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeNoWaitField'),array('arguments', 'consume', '\\amqp_091\\protocol\\basic\\ConsumeArgumentsField'),array('consumer-tag', 'consume-ok', '\\amqp_091\\protocol\\basic\\ConsumeOkConsumerTagField'),array('consumer-tag', 'cancel', '\\amqp_091\\protocol\\basic\\CancelConsumerTagField'),array('no-wait', 'cancel', '\\amqp_091\\protocol\\basic\\CancelNoWaitField'),array('consumer-tag', 'cancel-ok', '\\amqp_091\\protocol\\basic\\CancelOkConsumerTagField'),array('reserved-1', 'publish', '\\amqp_091\\protocol\\basic\\PublishReserved1Field'),array('exchange', 'publish', '\\amqp_091\\protocol\\basic\\PublishExchangeField'),array('routing-key', 'publish', '\\amqp_091\\protocol\\basic\\PublishRoutingKeyField'),array('mandatory', 'publish', '\\amqp_091\\protocol\\basic\\PublishMandatoryField'),array('immediate', 'publish', '\\amqp_091\\protocol\\basic\\PublishImmediateField'),array('reply-code', 'return', '\\amqp_091\\protocol\\basic\\ReturnReplyCodeField'),array('reply-text', 'return', '\\amqp_091\\protocol\\basic\\ReturnReplyTextField'),array('exchange', 'return', '\\amqp_091\\protocol\\basic\\ReturnExchangeField'),array('routing-key', 'return', '\\amqp_091\\protocol\\basic\\ReturnRoutingKeyField'),array('consumer-tag', 'deliver', '\\amqp_091\\protocol\\basic\\DeliverConsumerTagField'),array('delivery-tag', 'deliver', '\\amqp_091\\protocol\\basic\\DeliverDeliveryTagField'),array('redelivered', 'deliver', '\\amqp_091\\protocol\\basic\\DeliverRedeliveredField'),array('exchange', 'deliver', '\\amqp_091\\protocol\\basic\\DeliverExchangeField'),array('routing-key', 'deliver', '\\amqp_091\\protocol\\basic\\DeliverRoutingKeyField'),array('reserved-1', 'get', '\\amqp_091\\protocol\\basic\\GetReserved1Field'),array('queue', 'get', '\\amqp_091\\protocol\\basic\\GetQueueField'),array('no-ack', 'get', '\\amqp_091\\protocol\\basic\\GetNoAckField'),array('delivery-tag', 'get-ok', '\\amqp_091\\protocol\\basic\\GetOkDeliveryTagField'),array('redelivered', 'get-ok', '\\amqp_091\\protocol\\basic\\GetOkRedeliveredField'),array('exchange', 'get-ok', '\\amqp_091\\protocol\\basic\\GetOkExchangeField'),array('routing-key', 'get-ok', '\\amqp_091\\protocol\\basic\\GetOkRoutingKeyField'),array('message-count', 'get-ok', '\\amqp_091\\protocol\\basic\\GetOkMessageCountField'),array('reserved-1', 'get-empty', '\\amqp_091\\protocol\\basic\\GetEmptyReserved1Field'),array('delivery-tag', 'ack', '\\amqp_091\\protocol\\basic\\AckDeliveryTagField'),array('multiple', 'ack', '\\amqp_091\\protocol\\basic\\AckMultipleField'),array('delivery-tag', 'reject', '\\amqp_091\\protocol\\basic\\RejectDeliveryTagField'),array('requeue', 'reject', '\\amqp_091\\protocol\\basic\\RejectRequeueField'),array('requeue', 'recover-async', '\\amqp_091\\protocol\\basic\\RecoverAsyncRequeueField'),array('requeue', 'recover', '\\amqp_091\\protocol\\basic\\RecoverRequeueField'),array('delivery-tag', 'nack', '\\amqp_091\\protocol\\basic\\NackDeliveryTagField'),array('multiple', 'nack', '\\amqp_091\\protocol\\basic\\NackMultipleField'),array('requeue', 'nack', '\\amqp_091\\protocol\\basic\\NackRequeueField'));
}



class QosMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'qos';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('qos-ok');
    protected $fields = array('prefetch-size', 'prefetch-count', 'global');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class QosOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'qos-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class ConsumeMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'consume';
    protected $index = 20;
    protected $synchronous = true;
    protected $responseMethods = array('consume-ok');
    protected $fields = array('reserved-1', 'queue', 'consumer-tag', 'no-local', 'no-ack', 'exclusive', 'no-wait', 'arguments');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class ConsumeOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'consume-ok';
    protected $index = 21;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('consumer-tag');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CancelMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'cancel';
    protected $index = 30;
    protected $synchronous = true;
    protected $responseMethods = array('cancel-ok');
    protected $fields = array('consumer-tag', 'no-wait');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = true;
}
  
class CancelOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'cancel-ok';
    protected $index = 31;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('consumer-tag');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class PublishMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'publish';
    protected $index = 40;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('reserved-1', 'exchange', 'routing-key', 'mandatory', 'immediate');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = true;
    protected $hasNoWait = false;
}
  
class ReturnMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'return';
    protected $index = 50;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('reply-code', 'reply-text', 'exchange', 'routing-key');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = true;
    protected $hasNoWait = false;
}
  
class DeliverMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'deliver';
    protected $index = 60;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('consumer-tag', 'delivery-tag', 'redelivered', 'exchange', 'routing-key');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = true;
    protected $hasNoWait = false;
}
  
class GetMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'get';
    protected $index = 70;
    protected $synchronous = true;
    protected $responseMethods = array('get-ok', 'get-empty');
    protected $fields = array('reserved-1', 'queue', 'no-ack');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class GetOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'get-ok';
    protected $index = 71;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('delivery-tag', 'redelivered', 'exchange', 'routing-key', 'message-count');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = true;
    protected $hasNoWait = false;
}
  
class GetEmptyMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'get-empty';
    protected $index = 72;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('reserved-1');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class AckMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'ack';
    protected $index = 80;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('delivery-tag', 'multiple');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class RejectMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'reject';
    protected $index = 90;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('delivery-tag', 'requeue');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class RecoverAsyncMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'recover-async';
    protected $index = 100;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('requeue');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class RecoverMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'recover';
    protected $index = 110;
    protected $synchronous = true;
    protected $responseMethods = array('recover-ok');
    protected $fields = array('requeue');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class RecoverOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'recover-ok';
    protected $index = 111;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class NackMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'basic';
    protected $name = 'nack';
    protected $index = 120;
    protected $synchronous = false;
    protected $responseMethods = array();
    protected $fields = array('delivery-tag', 'multiple', 'requeue');
    protected $methFact = '\\amqp_091\\protocol\\basic\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\basic\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class ContentTypeField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'content-type'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class ContentEncodingField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'content-encoding'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class HeadersField extends \amqp_091\protocol\TableDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'headers'; }
    function getSpecFieldDomain() { return 'table'; }

}

  
class DeliveryModeField extends \amqp_091\protocol\OctetDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'delivery-mode'; }
    function getSpecFieldDomain() { return 'octet'; }

}

  
class PriorityField extends \amqp_091\protocol\OctetDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'priority'; }
    function getSpecFieldDomain() { return 'octet'; }

}

  
class CorrelationIdField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'correlation-id'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class ReplyToField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reply-to'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class ExpirationField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'expiration'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class MessageIdField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'message-id'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class TimestampField extends \amqp_091\protocol\TimestampDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'timestamp'; }
    function getSpecFieldDomain() { return 'timestamp'; }

}

  
class TypeField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'type'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class UserIdField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'user-id'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class AppIdField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'app-id'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class ClusterIdField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'cluster-id'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class QosPrefetchSizeField extends \amqp_091\protocol\LongDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'prefetch-size'; }
    function getSpecFieldDomain() { return 'long'; }

}

  
class QosPrefetchCountField extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'prefetch-count'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class QosGlobalField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'global'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class ConsumeReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class ConsumeQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

}

  
class ConsumeConsumerTagField extends \amqp_091\protocol\ConsumerTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'consumer-tag'; }
    function getSpecFieldDomain() { return 'consumer-tag'; }

}

  
class ConsumeNoLocalField extends \amqp_091\protocol\NoLocalDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-local'; }
    function getSpecFieldDomain() { return 'no-local'; }

}

  
class ConsumeNoAckField extends \amqp_091\protocol\NoAckDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-ack'; }
    function getSpecFieldDomain() { return 'no-ack'; }

}

  
class ConsumeExclusiveField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exclusive'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class ConsumeNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class ConsumeArgumentsField extends \amqp_091\protocol\TableDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'arguments'; }
    function getSpecFieldDomain() { return 'table'; }

}

  
class ConsumeOkConsumerTagField extends \amqp_091\protocol\ConsumerTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'consumer-tag'; }
    function getSpecFieldDomain() { return 'consumer-tag'; }

}

  
class CancelConsumerTagField extends \amqp_091\protocol\ConsumerTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'consumer-tag'; }
    function getSpecFieldDomain() { return 'consumer-tag'; }

}

  
class CancelNoWaitField extends \amqp_091\protocol\NoWaitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-wait'; }
    function getSpecFieldDomain() { return 'no-wait'; }

}

  
class CancelOkConsumerTagField extends \amqp_091\protocol\ConsumerTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'consumer-tag'; }
    function getSpecFieldDomain() { return 'consumer-tag'; }

}

  
class PublishReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class PublishExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class PublishRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class PublishMandatoryField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'mandatory'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class PublishImmediateField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'immediate'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class ReturnReplyCodeField extends \amqp_091\protocol\ReplyCodeDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reply-code'; }
    function getSpecFieldDomain() { return 'reply-code'; }

}

  
class ReturnReplyTextField extends \amqp_091\protocol\ReplyTextDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reply-text'; }
    function getSpecFieldDomain() { return 'reply-text'; }

}

  
class ReturnExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class ReturnRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class DeliverConsumerTagField extends \amqp_091\protocol\ConsumerTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'consumer-tag'; }
    function getSpecFieldDomain() { return 'consumer-tag'; }

}

  
class DeliverDeliveryTagField extends \amqp_091\protocol\DeliveryTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'delivery-tag'; }
    function getSpecFieldDomain() { return 'delivery-tag'; }

}

  
class DeliverRedeliveredField extends \amqp_091\protocol\RedeliveredDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'redelivered'; }
    function getSpecFieldDomain() { return 'redelivered'; }

}

  
class DeliverExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class DeliverRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class GetReserved1Field extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class GetQueueField extends \amqp_091\protocol\QueueNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'queue'; }
    function getSpecFieldDomain() { return 'queue-name'; }

}

  
class GetNoAckField extends \amqp_091\protocol\NoAckDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'no-ack'; }
    function getSpecFieldDomain() { return 'no-ack'; }

}

  
class GetOkDeliveryTagField extends \amqp_091\protocol\DeliveryTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'delivery-tag'; }
    function getSpecFieldDomain() { return 'delivery-tag'; }

}

  
class GetOkRedeliveredField extends \amqp_091\protocol\RedeliveredDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'redelivered'; }
    function getSpecFieldDomain() { return 'redelivered'; }

}

  
class GetOkExchangeField extends \amqp_091\protocol\ExchangeNameDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'exchange'; }
    function getSpecFieldDomain() { return 'exchange-name'; }

}

  
class GetOkRoutingKeyField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'routing-key'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class GetOkMessageCountField extends \amqp_091\protocol\MessageCountDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'message-count'; }
    function getSpecFieldDomain() { return 'message-count'; }

}

  
class GetEmptyReserved1Field extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class AckDeliveryTagField extends \amqp_091\protocol\DeliveryTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'delivery-tag'; }
    function getSpecFieldDomain() { return 'delivery-tag'; }

}

  
class AckMultipleField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'multiple'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class RejectDeliveryTagField extends \amqp_091\protocol\DeliveryTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'delivery-tag'; }
    function getSpecFieldDomain() { return 'delivery-tag'; }

}

  
class RejectRequeueField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'requeue'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class RecoverAsyncRequeueField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'requeue'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class RecoverRequeueField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'requeue'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class NackDeliveryTagField extends \amqp_091\protocol\DeliveryTagDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'delivery-tag'; }
    function getSpecFieldDomain() { return 'delivery-tag'; }

}

  
class NackMultipleField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'multiple'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class NackRequeueField extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'requeue'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  