#!/usr/bin/php
<?php
chdir (dirname (__FILE__) . "/../..");
require_once("./includes/init.php");
$opts = getopt("m:i:f::d::h::", array("model:","id:","field::","dump::","help::"));

if (isset($opts['h']) || isset($opts['help'])) {
	echo "Command Options:\n";
	echo __FILE__ . " --model=modelName --id=N [--field=fieldName] [--dump]\n";
	echo "model: Name of the model to load.\nid: Record # of the model you wish to display.\nfield: display a specific encrypted field, otherwise it displays the whole object.\ndump: Dumps the data as JSON instead of the default.\n";
	echo "See Also: https://dev.giftingapp.com/index.php/PII_Data_Encrytion/Decryption\n";
	exit (0);
} else if(!isset($opts['model']) || !isset($opts['id'])) { 
	echo "Invalid command.\nTry: " . __FILE__ . " -h\n";
	exit (1);
}


$className = $opts['model'].'Model';
$id = $opts['id'];
$dump = $opts['dump'];
$obj = new $className($opts['id']);

if(isset($opts['field'])) { 
	$field = $opts['field'];
	echo "\nCLASS: $className\nID: $id\nField: $field\n\n";
	if(isset($dump)) { 
		echo json_format(json_encode($obj->$field)) . "\n\n";
	}
	else {
		echo $obj->$field . "\n\n";
	}
}
else {
	echo json_format(json_encode($obj)) . "\n\n";
}



function json_format($json) 
{ 
    $tab = "  "; 
    $new_json = ""; 
    $indent_level = 0; 
    $in_string = false; 

    $json_obj = json_decode($json); 

    if($json_obj === false) 
        return false; 

    $json = json_encode($json_obj); 
    $len = strlen($json); 

    for($c = 0; $c < $len; $c++) 
    { 
        $char = $json[$c]; 
        switch($char) 
        { 
            case '{': 
            case '[': 
                if(!$in_string) 
                { 
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1); 
                    $indent_level++; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case '}': 
            case ']': 
                if(!$in_string) 
                { 
                    $indent_level--; 
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case ',': 
                if(!$in_string) 
                { 
                    $new_json .= ",\n" . str_repeat($tab, $indent_level); 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case ':': 
                if(!$in_string) 
                { 
                    $new_json .= ": "; 
                } 
                else 
                { 
                    $new_json .= $char; 
                } 
                break; 
            case '"': 
                if($c > 0 && $json[$c-1] != '\\') 
                { 
                    $in_string = !$in_string; 
                } 
            default: 
                $new_json .= $char; 
                break;                    
        } 
    } 

    return $new_json; 
} 
