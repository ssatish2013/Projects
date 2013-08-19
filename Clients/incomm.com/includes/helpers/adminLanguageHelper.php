<?php

class adminLanguageHelper {

    private function scrubValue( $value ) {
        return preg_replace_callback('/\{|\}/', function( $char ) {

            $scrub = array(
                '{' => '{ldelim}',
                '}' => '{rdelim}'
            );
            return $scrub[ $char[0] ];

        }, $value );

    }

    public function save() {

        $lang = new languageModel();
        $lang->name = request::unsignedPost('name');
        $lang->partner = globals::partner();
        $lang->load();
        $lang->value = adminLanguageHelper::scrubValue( request::unsignedPost('value') );
        $lang->save();

        return json_encode( array(
            "success" => true
        ));
    }

    public function toggle() {

        $_SESSION['languageEditMode'] = ! $_SESSION['languageEditMode'];

        return json_encode( array(
            "status" => $_SESSION['languageEditMode']
        ));
    }

}
