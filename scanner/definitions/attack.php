<?php
abstract class Attack {
    const Xss='xss';
    const SqlInjection='sql';
    const DirectoryTraversal ='dir';
    const HeaderInjection = 'header';
    const EmailInjection = 'email';
    const CodeExecution = 'code';
    const SystemExcution = 'system';
    const CallBack = 'callback';
    const FileManipulation = 'file';
    
    public static function get($name) {
        switch(strtolower($name)) {
            case 'xss':
            case 'crosssitescripting':
                return self::Xss;
                break;
            case 'sql':
            case 'sqlinjection':
                return self::SqlInjection;
                break;
            // TODO
        }
    }
}
