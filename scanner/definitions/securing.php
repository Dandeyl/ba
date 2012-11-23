<?php
/**
 * Possible ways to secure an argument.
 */
abstract class Securing {
    const SpecialCharsEncode = 'SpecialCharsEncode';
    const StripTags = 'StripTags';
    const AddSlashes = 'AddSlashes';
    const EscapeShellCmd = 'EscapeShellCmd';
    const NotUserDefined = 'NotUserDefined';
    const Base64Encode = 'Base64Encode';
    
    public static function get($vuln){
        switch(strtolower($vuln)) {
            case 'specialcharsencode':
                return self::SpecialCharsEncode;
            case 'striptags':
                return self::StripTags;
            case 'addslashes':
                return self::AddSlashes;
            case 'shellescape':
            case 'escapeshellcmd':
                return self::EscapeShellCmd;
            case 'notuserdefined':
                return self::NotUserDefined;
            case 'base64':
            case 'base64encode':
                return self::Base64Encode;
        }
    }
}
