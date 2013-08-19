<?php
namespace amqp_091\protocol\connection;
/** Ampq binding code, generated from doc version 0.9.1 */
class ConnectionClass extends \amqp_091\protocol\abstrakt\XmlSpecClass
{
    protected $name = 'connection';
    protected $index = 10;
    protected $fields = array();
    protected $methods = array(10 => 'start',11 => 'start-ok',20 => 'secure',21 => 'secure-ok',30 => 'tune',31 => 'tune-ok',40 => 'open',41 => 'open-ok',50 => 'close',51 => 'close-ok');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
}

abstract class MethodFactory extends \amqp_091\protocol\abstrakt\MethodFactory
{
    protected static $Cache = array(array(10, 'start', '\\amqp_091\\protocol\\connection\\StartMethod'),array(11, 'start-ok', '\\amqp_091\\protocol\\connection\\StartOkMethod'),array(20, 'secure', '\\amqp_091\\protocol\\connection\\SecureMethod'),array(21, 'secure-ok', '\\amqp_091\\protocol\\connection\\SecureOkMethod'),array(30, 'tune', '\\amqp_091\\protocol\\connection\\TuneMethod'),array(31, 'tune-ok', '\\amqp_091\\protocol\\connection\\TuneOkMethod'),array(40, 'open', '\\amqp_091\\protocol\\connection\\OpenMethod'),array(41, 'open-ok', '\\amqp_091\\protocol\\connection\\OpenOkMethod'),array(50, 'close', '\\amqp_091\\protocol\\connection\\CloseMethod'),array(51, 'close-ok', '\\amqp_091\\protocol\\connection\\CloseOkMethod'));
}

abstract class FieldFactory  extends \amqp_091\protocol\abstrakt\FieldFactory
{
    protected static $Cache = array(array('version-major', 'start', '\\amqp_091\\protocol\\connection\\StartVersionMajorField'),array('version-minor', 'start', '\\amqp_091\\protocol\\connection\\StartVersionMinorField'),array('server-properties', 'start', '\\amqp_091\\protocol\\connection\\StartServerPropertiesField'),array('mechanisms', 'start', '\\amqp_091\\protocol\\connection\\StartMechanismsField'),array('locales', 'start', '\\amqp_091\\protocol\\connection\\StartLocalesField'),array('client-properties', 'start-ok', '\\amqp_091\\protocol\\connection\\StartOkClientPropertiesField'),array('mechanism', 'start-ok', '\\amqp_091\\protocol\\connection\\StartOkMechanismField'),array('response', 'start-ok', '\\amqp_091\\protocol\\connection\\StartOkResponseField'),array('locale', 'start-ok', '\\amqp_091\\protocol\\connection\\StartOkLocaleField'),array('challenge', 'secure', '\\amqp_091\\protocol\\connection\\SecureChallengeField'),array('response', 'secure-ok', '\\amqp_091\\protocol\\connection\\SecureOkResponseField'),array('channel-max', 'tune', '\\amqp_091\\protocol\\connection\\TuneChannelMaxField'),array('frame-max', 'tune', '\\amqp_091\\protocol\\connection\\TuneFrameMaxField'),array('heartbeat', 'tune', '\\amqp_091\\protocol\\connection\\TuneHeartbeatField'),array('channel-max', 'tune-ok', '\\amqp_091\\protocol\\connection\\TuneOkChannelMaxField'),array('frame-max', 'tune-ok', '\\amqp_091\\protocol\\connection\\TuneOkFrameMaxField'),array('heartbeat', 'tune-ok', '\\amqp_091\\protocol\\connection\\TuneOkHeartbeatField'),array('virtual-host', 'open', '\\amqp_091\\protocol\\connection\\OpenVirtualHostField'),array('reserved-1', 'open', '\\amqp_091\\protocol\\connection\\OpenReserved1Field'),array('reserved-2', 'open', '\\amqp_091\\protocol\\connection\\OpenReserved2Field'),array('reserved-1', 'open-ok', '\\amqp_091\\protocol\\connection\\OpenOkReserved1Field'),array('reply-code', 'close', '\\amqp_091\\protocol\\connection\\CloseReplyCodeField'),array('reply-text', 'close', '\\amqp_091\\protocol\\connection\\CloseReplyTextField'),array('class-id', 'close', '\\amqp_091\\protocol\\connection\\CloseClassIdField'),array('method-id', 'close', '\\amqp_091\\protocol\\connection\\CloseMethodIdField'));
}



class StartMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'start';
    protected $index = 10;
    protected $synchronous = true;
    protected $responseMethods = array('start-ok');
    protected $fields = array('version-major', 'version-minor', 'server-properties', 'mechanisms', 'locales');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class StartOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'start-ok';
    protected $index = 11;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('client-properties', 'mechanism', 'response', 'locale');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class SecureMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'secure';
    protected $index = 20;
    protected $synchronous = true;
    protected $responseMethods = array('secure-ok');
    protected $fields = array('challenge');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class SecureOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'secure-ok';
    protected $index = 21;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('response');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class TuneMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'tune';
    protected $index = 30;
    protected $synchronous = true;
    protected $responseMethods = array('tune-ok');
    protected $fields = array('channel-max', 'frame-max', 'heartbeat');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class TuneOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'tune-ok';
    protected $index = 31;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('channel-max', 'frame-max', 'heartbeat');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class OpenMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'open';
    protected $index = 40;
    protected $synchronous = true;
    protected $responseMethods = array('open-ok');
    protected $fields = array('virtual-host', 'reserved-1', 'reserved-2');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class OpenOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'open-ok';
    protected $index = 41;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array('reserved-1');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CloseMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'close';
    protected $index = 50;
    protected $synchronous = true;
    protected $responseMethods = array('close-ok');
    protected $fields = array('reply-code', 'reply-text', 'class-id', 'method-id');
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class CloseOkMethod extends \amqp_091\protocol\abstrakt\XmlSpecMethod
{
    protected $class = 'connection';
    protected $name = 'close-ok';
    protected $index = 51;
    protected $synchronous = true;
    protected $responseMethods = array();
    protected $fields = array();
    protected $methFact = '\\amqp_091\\protocol\\connection\\MethodFactory';
    protected $fieldFact = '\\amqp_091\\protocol\\connection\\FieldFactory';
    protected $classFact = '\\amqp_091\\protocol\\ClassFactory';
    protected $content = false;
    protected $hasNoWait = false;
}
  
class StartVersionMajorField extends \amqp_091\protocol\OctetDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'version-major'; }
    function getSpecFieldDomain() { return 'octet'; }

}

  
class StartVersionMinorField extends \amqp_091\protocol\OctetDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'version-minor'; }
    function getSpecFieldDomain() { return 'octet'; }

}

  
class StartServerPropertiesField extends \amqp_091\protocol\PeerPropertiesDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'server-properties'; }
    function getSpecFieldDomain() { return 'peer-properties'; }

}

  
class StartMechanismsField extends \amqp_091\protocol\LongstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'mechanisms'; }
    function getSpecFieldDomain() { return 'longstr'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class StartLocalesField extends \amqp_091\protocol\LongstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'locales'; }
    function getSpecFieldDomain() { return 'longstr'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class StartOkClientPropertiesField extends \amqp_091\protocol\PeerPropertiesDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'client-properties'; }
    function getSpecFieldDomain() { return 'peer-properties'; }

}

  
class StartOkMechanismField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'mechanism'; }
    function getSpecFieldDomain() { return 'shortstr'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class StartOkResponseField extends \amqp_091\protocol\LongstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'response'; }
    function getSpecFieldDomain() { return 'longstr'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class StartOkLocaleField extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'locale'; }
    function getSpecFieldDomain() { return 'shortstr'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class SecureChallengeField extends \amqp_091\protocol\LongstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'challenge'; }
    function getSpecFieldDomain() { return 'longstr'; }

}

  
class SecureOkResponseField extends \amqp_091\protocol\LongstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'response'; }
    function getSpecFieldDomain() { return 'longstr'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject));
    }

}

  
class TuneChannelMaxField extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'channel-max'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class TuneFrameMaxField extends \amqp_091\protocol\LongDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'frame-max'; }
    function getSpecFieldDomain() { return 'long'; }

}

  
class TuneHeartbeatField extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'heartbeat'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class TuneOkChannelMaxField extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'channel-max'; }
    function getSpecFieldDomain() { return 'short'; }

    function validate($subject) {
        return (parent::validate($subject) && ! is_null($subject) && true);
    }

}

  
class TuneOkFrameMaxField extends \amqp_091\protocol\LongDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'frame-max'; }
    function getSpecFieldDomain() { return 'long'; }

}

  
class TuneOkHeartbeatField extends \amqp_091\protocol\ShortDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'heartbeat'; }
    function getSpecFieldDomain() { return 'short'; }

}

  
class OpenVirtualHostField extends \amqp_091\protocol\PathDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'virtual-host'; }
    function getSpecFieldDomain() { return 'path'; }

}

  
class OpenReserved1Field extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'shortstr'; }

}

  
class OpenReserved2Field extends \amqp_091\protocol\BitDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-2'; }
    function getSpecFieldDomain() { return 'bit'; }

}

  
class OpenOkReserved1Field extends \amqp_091\protocol\ShortstrDomain implements \amqp_091\protocol\abstrakt\XmlSpecField
{
    function getSpecFieldName() { return 'reserved-1'; }
    function getSpecFieldDomain() { return 'shortstr'; }

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

  