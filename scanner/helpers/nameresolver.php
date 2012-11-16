<?php

/**
 * Class that helps resolving the qualified names of expressions. E.g.
 * ${$func("dir/f")} will be resolved to $<name>
 */
class Helper_NameResolver {
    
    /**
     * Return the real name of the expression. $$var will be resolved to
     * $<content-of-$var>. If $var is not set NULL will be returned and a warning
     * get logged.
     * If it can not be resolved, false will be returned.
     * @param PHPParser_Node_Expr $name Name to be resolved
     * @return string|false|null Name of the variabl
     */
    public static function resolve(PHPParser_Node $expr) {
        // does the expr have a name?
        if(isset($expr->namespacedName)) {
            return implode("\\", $expr->namespacedName->parts);
        }
        elseif(isset($expr->name)) {
            $name = self::resolveRecursive($expr);
        }
        elseif($expr instanceof PHPParser_Node_Expr_ArrayDimFetch) {
            $name = self::resolve($expr->var);
        }
        else {
            throw new Exception("The passed object does not have a name attribute");
        }
        
        if($expr instanceof PHPParser_Node_Expr_Variable 
           && $name != false)
        {
            $name = '$'.$name;
        }
        
        return $name;
    }
    
    /**
     * Resolve the name recursively.
     * @param PHPParser_Node_Expr $var
     * @return null
     * @throws Exception
     */
    private static function resolveRecursive(PHPParser_Node $var) {
        if(is_string($var->name)) {
            return $var->name;
        }
        elseif(isset($var->name->parts) and is_array($var->name->parts)) {
            return implode("\\", $var->name->parts);
        }
        // variable
        elseif($var->name instanceof PHPParser_Node_Expr_Variable) {
            $name = self::resolve($var->name); // TODO: call method "resolve"?
            $variable  = ScanInfo::findVar($name);
            
            if($variable instanceof Obj_Variable) {
                $value = $variable->getValue();
                return (!$value) ? false : $value; //return false if value of variable could not be resolved
            }
            else {
                ScanInfo::addWarning(Warning::VariableNotInitialised, $var);
            }
            return null; // return null if variable could not be found
        }
        
        // function
        elseif($var->name instanceof PHPParser_Node_Expr_FuncCall) {
            $name = self::resolveRecursive($var->name); 
            return self::$functions->findFunction($name)->getValue();
        }
        
        // error
        else {
            $err = 'Helper_NameResolver: unable to resolve name.';
            throw new Exception($err);
        }
    }
    
    
    /**
     * Sets a list of currently available variables (and constants and properties).
     * @param Varlist $varlist
     *
    public static function setVarlist(Varlist &$varlist) {
        self::$varlist = &$varlist;
        self::$varlist_set = true;
    }
    
    /**
     * Sets a list of currently available functions (and methods).
     * @param type $functions
     *
    public static function setFunctions(&$functions) {
        self::$functions = &$functions;
        self::$functions_set = true;
    }*/
    
}

#class Helper_NameResolver_UnableToResolveException extends Exception {}
#class Helper_NameResolver_UninitialisedException extends Exception {}