<?php
/**
 * Scans the expression. Checks if it is vulnerable, securing, executable etc.
 */
class Helper_ExpressionResolver {
    /**
     * The Obj_Resolved object that gets returned;
     * @var Obj_Resolved 
     */
    protected $obj_resolved;
    
    /**
     * Protected constructor. Only gets called from Helper_ExpressionResolver::resolve
     * @param PHPParser_Node_Expr $expr
     */
    protected function __construct(PHPParser_Node_Expr &$expr) {
        // Initialise the Obj_Resolved object
        $resolved = &$this->obj_resolved;
        $resolved = new Obj_Resolved;
        
        $resolved->setExecutable(false);
        $resolved->setExpression($expr);
        $resolved->setReturnType(false);
        $resolved->setUserDefined(false);
        
        if($expr instanceof PHPParser_Node_Scalar_Encapsed) {
            $this->resolveScalarEncapsed($expr);
        }
        elseif($expr instanceof PHPParser_Node_Scalar) {
            $this->resolveScalar($expr);
        }
        // => EXPR_VARIABLE          $variable
        elseif($expr instanceof PHPParser_Node_Expr_Variable) {
            $this->resolveExprVariable($expr);
        }
        // => EXPR_ARRAY
        elseif($expr instanceof PHPParser_Node_Expr_Array) {
            $this->resolveExprArray($expr);
        }
        // => EXPR_ARRAYDIMFETCH     $array[$dimension]
        elseif($expr instanceof PHPParser_Node_Expr_ArrayDimFetch) {
            $this->resolveExprArrayDimFetch($expr);
        }
        // => EXPR_ARRAYITEM
        elseif($expr instanceof PHPParser_Node_Expr_ArrayItem) {
            $this->resolveExprArrayItem($expr);
        }
        
        elseif($expr instanceof PHPParser_Node_Expr_ConstFetch) {
            $this->resolveExprConstFetch($expr);
        }
        elseif($expr instanceof PHPParser_Node_Expr_Concat) {
            $this->resolveExprConcat($expr);
        }
        // Assignment
        elseif(Action_Assignments::isAssignment($expr)) {
            return Action_Assignments::leaveNode($expr);
        }
        elseif($expr instanceof PHPParser_Node_Expr_FuncCall) {
            $this->resolveExprFunctionCall($expr);
        }
        elseif($expr instanceof PHPParser_Node_Expr_BooleanNot) {
            $resolve_expr = Helper_ExpressionResolver::resolve($expr->expr);
            $resolved->setReturnType('bool');
            $resolved->setValue(!((bool) $resolve_expr->getValue()));
        }
        // OR            ||
        elseif(   $expr instanceof PHPParser_Node_Expr_LogicalOr 
               || $expr instanceof PHPParser_Node_Expr_BooleanOr) {
            $this->resolveExprLogicalOr($expr);
        }
        
        elseif($expr instanceof PHPParser_Node_Expr_NotEqual) {
            $left = Helper_ExpressionResolver::resolve($expr->left);
            $right = Helper_ExpressionResolver::resolve($expr->right);
            
            $resolved->setReturnType("bool");
            $resolved->setUserDefined($this->argIsUserdefined(array($left, $right)));
            $resolved->setValue($left->getValue() != $right->getValue());
            
        }
        elseif($expr instanceof PHPParser_Node_Expr_Equal) {
            $left = Helper_ExpressionResolver::resolve($expr->left);
            $right = Helper_ExpressionResolver::resolve($expr->right);
            
            $resolved->setReturnType("bool");
            $resolved->setUserDefined($this->argIsUserdefined(array($left, $right)));
            $resolved->setValue($left->getValue() == $right->getValue());
        }
        elseif($expr instanceof PHPParser_Node_Expr_Plus) {
            $left = Helper_ExpressionResolver::resolve($expr->left);
            $right = Helper_ExpressionResolver::resolve($expr->right);
            
            if(!$left->isUserDefined() && !$right->isUserDefined()) {
                if($left->getReturnType() == "array" && $right->getReturnType() == "array") {
                    if(empty($left->getValue())) {
                        $resolved->setValue($right->getValue());
                    }
                    $resolved->setValue($left->getValue());
                }
                else {
                    $resolved->setValue($left->getValue() + $right->getValue());
                }
            }
            else {
                $resolved->setUserDefined($this->argIsUserdefined(array($left, $right)));
            }
        }
        else {
            throw new Exception("Helper_ExpressionResolver: Expression of type ".  get_class($expr) ." can not be handled yet!");
        }
        
    }
    
    /**
     * Resolve scalars.
     * @param PHPParser_Node_Expr $expr
     */
    protected function resolveScalar(PHPParser_Node_Scalar $expr) {
        $val = eval('return '.Scanner::prettyPrintExpr($expr).';');
        $this->obj_resolved->setValue($val);
        $this->obj_resolved->setExecutable(true);
        $this->obj_resolved->setReturnType(true);
    }
    
       /**
     * Resolve encapsed scalars.
     * @param PHPParser_Node_Scalar_Encapsed $expr
     */
    protected function resolveScalarEncapsed(PHPParser_Node_Scalar_Encapsed $expr) {
        $parts = array();
        $value = '';
        
        foreach($expr->parts as $p) {
            if(is_object($p)) {
                $parts[] = $resolved = Helper_ExpressionResolver::resolve($p);
                if(!$resolved->isUserDefined()) {
                    $value .= $resolved->getValue();
                }
            }
            else {
                $value .= $p;
            }
        }
        
        $this->obj_resolved->setValue($value);
        $this->obj_resolved->setExecutable(empty($parts));
        $this->obj_resolved->setUserDefined($this->argIsUserdefined($parts));
        $this->obj_resolved->setSecuredBy($this->argIsSecuredBy($parts));
    }
    
    
    /**
     * Resolve variables
     * @param PHPParser_Node_Expr_Variable $expr
     */
    protected function resolveExprVariable(PHPParser_Node_Expr_Variable $expr) {
        $var_name = Helper_NameResolver::resolve($expr);
        
        if($var_name) {
            $variable = ScanInfo::findVar($var_name);

            if($variable) {
                /*@var $variable Obj_Variable */
                $this->obj_resolved->setUserDefined($variable->isUserDefined());
                $this->obj_resolved->setSecuredBy($variable->getSecuredBy());
                $this->obj_resolved->setValue($variable->getValue());
                $this->obj_resolved->setExecutable(false);
                
                
            }
            else {
                // Variable uninitialised!
                ScanInfo::addWarning(
                        Warning::VariableNotInitialised, 
                        $expr
                       );
                $this->obj_resolved->setUserDefined(true);
                $this->obj_resolved->setValue(null);
            }
        }
    }
    
    
    /**
     * Resolve concatenations.
     * @param PHPParser_Node_Expr $expr
     */
    protected function resolveExprConcat(PHPParser_Node_Expr_Concat $expr) {
        $left  = Helper_ExpressionResolver::resolve($expr->left);
        $right = Helper_ExpressionResolver::resolve($expr->right);
        
        $this->obj_resolved->setExecutable($left->isExecutable() && $right->isExecutable());
        $this->obj_resolved->setReturnType($left->getReturnType() && $right->getReturnType());
        #$resolved->setSecuredFor(array());
        $this->obj_resolved->setUserDefined($this->argIsUserdefined(array($left, $right)));
        
        // Both expressions already have a value
        if($left->getValue() && $right->getValue()){
            $this->obj_resolved->setValue($left->getValue().$right->getValue());
        }
        // Both expressions are executable
        elseif($this->obj_resolved->isExecutable()) {
            $left_expr  = $this->resolveExprConcatPrepareResolvedForEval($left);
            $right_expr = $this->resolveExprConcatPrepareResolvedForEval($right);
            
            $cmd = $left_expr.'.'.$right_expr.';';
            echo '>>>>>>>> return '.$cmd;
            $value = eval($cmd);
            $this->obj_resolved->setValue($value);
        }
    }
    
    
    /**
     * Resolve logical OR's. $var1 || $var2
     * @param PHPParser_Node_Expr $expr
     */
    protected function resolveExprLogicalOr(PHPParser_Node_Expr $expr) {
        $left  = Helper_ExpressionResolver::resolve($expr->left);
        $right = Helper_ExpressionResolver::resolve($expr->right);
        
        $this->obj_resolved->setExecutable($left->isExecutable() && $right->isExecutable());
        $this->obj_resolved->setReturnType("bool");
        #$resolved->setSecuredFor(array());
        $this->obj_resolved->setUserDefined($this->argIsUserdefined(array($left, $right)));
        
        // Both expressions already have a value
        if($left->getValue() && $right->getValue()){
            $this->obj_resolved->setValue($left->getValue() || $right->getValue());
        }
        // Both expressions are executable
        elseif($this->obj_resolved->isExecutable()) {
            $left_expr  = $this->resolveExprConcatPrepareResolvedForEval($left);
            $right_expr = $this->resolveExprConcatPrepareResolvedForEval($right);
            
            $cmd = $left_expr.'.'.$right_expr.';';
            echo '>>>>>>>> return '.$cmd;
            $value = eval($cmd);
            $this->obj_resolved->setValue($value);
        }
    }
    
    protected function resolveExprConcatPrepareResolvedForEval(Obj_Resolved $resolved) {
        if($resolved->getvalue() !== null) { 
            return "'".$resolved->getValue()."'";
        }
        else {
            return Scanner::prettyPrintExpr($resolved->getExpression());
        }
    }
    
    
    /**
     * Resolve array definition.
     */
    protected function resolveExprArray(PHPParser_Node_Expr_Array $expr) {
        $arr_items = array();
        
        foreach($expr->items as $item) {
            $resolved_item = self::resolve($item);
            $arr_items[]   = $resolved_item; 
        }
    }
    
    protected function resolveExprArrayItem(PHPParser_Node_Expr_ArrayItem $item) {
        
    }
    
    /**
     * Resolve arrays.
     * @param PHPParser_Node_Expr $expr
     */
    protected function resolveExprArrayDimFetch(PHPParser_Node_Expr_ArrayDimFetch $expr) {
        if($expr->var instanceof PHPParser_Node_Expr_Variable) {
            $name = Helper_NameResolver::resolve($expr->var);
        }
        else {
            $name = $this->resolveExprArrayDimFetchVarNameRecursive($expr->var);
        }
        $dim  = Helper_ExpressionResolver::resolve($expr->dim);

        $var_name = $name.'["'.$dim->getValue().'"]';
        $variable = ScanInfo::findVar($var_name);

        if($variable) {
            /*@var $variable Obj_Variable */
            $this->obj_resolved->setUserDefined($variable->isUserDefined());
            $this->obj_resolved->setSecuredBy($variable->getSecuredBy());
            $this->obj_resolved->setValue($variable->getValue());
        }
        else {
            // Variable uninitialised!
            ScanInfo::addWarning(
                    'variable_uninitialised', 
                    ScanInfo::getCurrentFilePath(),
                    ScanInfo::getCurrentFileLine(),
                    $expr
                   );
        }
    }
    
    /**
     * Resolve the name of an array variable recursive. Needed for multi dimensional arrays
     * @param PHPParser_Node_Expr_ArrayDimFetch $expr
     * @return type
     */
    protected function resolveExprArrayDimFetchVarNameRecursive(PHPParser_Node_Expr $expr) {
        if($expr->var instanceof PHPParser_Node_Expr_Variable) {
            return Helper_NameResolver::resolve($expr->var); 
        }
        elseif($expr->var instanceof PHPParser_Node_Expr_ArrayDimFetch) {
            // recursive call..
            return $this->resolveExprArrayDimFetchVarNameRecursive($expr->var);
        }
        else {
            throw new Exception('Helper_Expressionresolver->resolveExprArrayDimFetchVarNameRecursive: Unexpected expression. '.var_export($expr, true));
        }
    }
    
    
    /**
     * Resolve constants.
     * @param PHPParser_Node_Expr_ConstFetch $expr
     */
    protected function resolveExprConstFetch(PHPParser_Node_Expr_ConstFetch $expr) {
        $name = Helper_NameResolver::resolve($expr);
        
        
        if($name) {
            $const = ScanInfo::findVar($name);

            if($const) {
                /*@var $const Obj_Variable */
                $this->obj_resolved->setUserDefined($const->isUserDefined());
                $this->obj_resolved->setSecuredBy($const->getSecuredBy());
                $this->obj_resolved->setValue($const->getValue());
                $this->obj_resolved->setExecutable(false);
            }
            else {
                // Variable uninitialised!
                ScanInfo::addWarning(
                        Warning::VariableNotInitialised, 
                        $expr
                       );
            }
        }
    }
    
    /**
     * Resolve function calls.
     * @param PHPParser_Node_Expr $expr
     */
    protected function resolveExprFunctionCall(PHPParser_Node_Expr_FuncCall $expr) {
        // get the name of the function and look it up
        $name = Helper_NameResolver::resolve($expr);
        $func = ScanInfo::findFunction($name);
        // if it's not defined (yet), add a notice to ScanInfo
        if(!$func) {
            ScanInfo::addNotFoundFunction($name);
            $this->resolveExprFunctionCallNotDefined($expr);
            return;
        }
        
        
        // if it is a system function: 
        //     execute it if it's safe, otherwise generate a random value
        if(!$func->isUserDefined()) {
            $this->resolveExprFunctionCallSystemDefined($func, $expr);
        }
        else {
            $this->resolveExprFunctionCallUserDefined($func, $expr);
        }
    }
    
    /**
     * Resolve function calls where functions are called, that are defined by php or extensions
     */
    protected function resolveExprFunctionCallSystemDefined(Obj_Function $func, $expr) {
        // get information about the functions parameters
        $resolved_arguments = array();
        $values      = array();
        $references  = array();
        $resolve_err = false;
        foreach($expr->args as $arg) {
            $val = self::resolve($arg->value);
            /*@var $val Obj_Resolved*/
            
            if(!$val->isResolveError()) {
                $resolved_arguments[] = $val;
                $values[]             = $val->getValue();
                $references[]         = $arg->byRef; 
            }
            else {
                $resolve_err = true;
                throw new Exception("Helper_ExpressionResolver: Error resolving an argument occured!");
            }
        }
        
        // get parameters
        $vulnerable = $this->getVulnerableParameters($func, $values);
        
        // return user defined?
        $this->obj_resolved->setUserDefined($this->argIsUserdefined($resolved_arguments));

        // get securing from parameters
        $param_indexes = $func->getReturnDefinedByParams();
        if($param_indexes !== null) {
            // if array is empty fill it with all arguments
            if(count($param_indexes) == 0) {
                $i = 1;
                foreach($resolved_arguments as $arg) {
                    $param_indexes[] = $i;
                    $i++;
                }
            }

            // check parameters
            $securings = array();
            $count_userdef_params = 0;
            foreach($param_indexes as $index) {
                if(!isset($resolved_arguments[($index-1)])) break;
                $resolved = $resolved_arguments[($index-1)];
                /* @var $resolved Obj_Resolved */
                if($resolved->isUserDefined()) {
                    $count_userdef_params++;
                    foreach($resolved->getSecuredBy() as $mechanism) {
                        isset($securings[$mechanism]) ? $securings[$mechanism]++ : $securings[$mechanism] = 1; 
                    }
                }
            }

            foreach($securings as $mechanism => $count) {
                if($count == $count_userdef_params) {
                    $this->obj_resolved->addSecuredBy($mechanism);
                }
            }
        }


        // securing..
        if(($securing = $func->getReturnAddSecuring()) != null) {
            $this->obj_resolved->addSecuredBy($securing);
        }

        // unsecuring
        if(($securing = $func->getReturnRemoveSecuring()) != null) {
            $this->obj_resolved->removeSecuredBy($securing);
        }

        // return type
        $this->obj_resolved->setReturnType($func->getReturnType());

        // EXECUTE
        if($func->isExecutable() && !$vulnerable) {
            // Todo: Fine Tuning ^^
            $this->obj_resolved->setValue(call_user_func_array($func->getName(), $values));
        }
        elseif(!$func->isExecutable() && !$vulnerable && $func->getFunctionReplacement()) {
            $this->obj_resolved->setValue(call_user_func_array($func->getFunctionReplacement(), $values));
        }

        elseif($vulnerable) {
            foreach($vulnerable as $argpos) {
                $arg = $resolved_arguments[($argpos-1)];
                if($arg->isUserDefined()) {
                    // we have a dangerous argument, check if it got secured
                    $attack = Attack::get($func->getVulnerableFor());
                    if(Attack_Handler::checkAttackCondition($attack, $resolved)) {
                        ScanInfo::addVulnerability($attack, $expr);
                    }
                }
            }
        }
    }
    
    /**
     * Get the state of userdefinement from multiple expressions.
     * Returns 0 if all expressions are not defined by the user
     * Returns 1 if at least one argument is directly user definable
     * Returns 2 if at least one argument depends on the content of db or file
     * (and none is directly user definable)
     */
    protected function argIsUserdefined(array $arguments) {
        $return_user_defined = 0;
        foreach($arguments as $arg) {
            if($arg->isUserDefined() === 1) {
                $return_user_defined = 1;
                break;
            }
            elseif($arg->isUserDefined() === 2) {
                $return_user_defined = 2;
            }
        }
        return $return_user_defined;
    }
    
    
    /**
     * Checks each user defined value if it somehow got secured. If all
     * user defined values are secured by a mechanism: add it to return array.
     * @return array
     */
    protected function argIsSecuredBy(array $arguments) {
        $protects = array();
        foreach($arguments as $arg) {
            /* @var $arg Obj_Resolved */
            if($arg->isUserDefined()) {
                $protects[] = $arg->getSecuredBy();
            }
        }
        
        if(empty($protects)) return array();
        if(count($protects) == 1) return $protects[0];
        return call_user_func_array("array_intersect", $protects);
    }
    
    /**
     * Resolve function calls where functions are called, that are defined by the script
     */
    protected function resolveExprFunctionCallUserDefined(Obj_Function $func, PHPParser_Node_Expr_FuncCall $expr) {
        
        $oldscope = ScanInfo::getScope();
        ScanInfo::setScope('Function#'.$func->getName());
        
        // Write function parameters in varlist
        foreach($func->getParameters() as $i => $func_param) {
            if(isset($expr->args[$i])) {
                $resolved_param = Helper_ExpressionResolver::resolve($expr->args[$i]->value);
                
                if($expr->args[$i]->byRef) {
                    $refvar = ScanInfo::findVar(Helper_NameResolver::resolve($expr->args[$i]->value), $oldscope);
                    $var = clone $refvar;
                    $var->setScope(ScanInfo::getScope());
                    $var->setName('$'.$func->param->name);
                    $var->setReferenceTo(refvar);
                }
                else {
                    $var = new Obj_Variable();
                    $var->setScope(ScanInfo::getScope());
                    $var->setName('$'.$func_param->name);
                    $var->setUserDefined($resolved_param->isUserDefined());
                    $var->setSecuredBy($resolved_param->getSecuredBy());
                    $var->setValue($resolved_param->getValue());
                }
                
                ScanInfo::addVar($var);
            }
            else {
                // this shouldn't happen if the source code is valid
                if(!$func_param->default) {
                    throw new Exception("ExpressionResolver: The function called expects more parameters than given");
                }
                $resolved_param = Helper_ExpressionResolver::resolve($func_param->default);
                
                $var = new Obj_Variable('$'.$func_param->name);
                $var->setValue($resolved_param->getValue());
                $var->setSecuredBy($resolved_param->getSecuredBy());
                $var->setUserDefined(false);
                
                ScanInfo::addVar($var);
            }
        }
        
        // Call user function statements
        return $func->getStatements();
    }
    
    
    
    
    /**
     * Resolve function calls where functions are called, that are not yet defined
     */
    protected function resolveExprFunctionCallNotDefined($expr) {
        // get information about the functions parameters
        $resolved_arguments = array();
        $values      = array();
        $references  = array();
        $resolve_err = false;
        foreach($expr->args as $arg) {
            $val = self::resolve($arg->value);
            /*@var $val Obj_Resolved*/
            
            if(!$val->isResolveError()) {
                $resolved_arguments[] = $val;
                $values[]             = $val->getValue();
                $references[]         = $arg->byRef; 
            }
            else {
                $resolve_err = true;
                throw new Exception("Helper_ExpressionResolver: Error resolving an argument occured!");
            }
        }
        
        $this->obj_resolved->setValue(null);
        
        // return user defined?
        $this->obj_resolved->setUserDefined($this->argIsUserdefined($resolved_arguments));
    }
    
    
    
    
    
    protected function getVulnerableParameters(Obj_Function $func, array $values) {
        if(empty($values)) {
            return false;
        }
        
        $return = false;
        // check if this function is vulnerable
        if(($func_vulnerable = $func->getVulnerableFor()) != null) {
            // vulnerable
            $return = $func->getVulnerableParameters();
            if(is_array($return) && empty($return)) {
                $return = range(1, count($values));
            }

            // are there functions to check if the function is vulnerable?
            $vuln_func_name = $func->getFunctionToCheckForVulnerability();
            if($vuln_func_name) {
                $vulnerable = false;

                if(call_user_func_array($vuln_func_name, $values)) {
                    $vulnerable = $func->getVulnerableParameters();
                }
            }
        }
        
        return $return;
    }
    
    
    
    /**
     * Returns the resolved object
     * @return Obj_Resolved
     */
    protected function getResolvedObj() {
        return $this->obj_resolved;
    }
    
    
    /**
     * Pass $node->expr to this method
     * @param PHPParser_Node_Expr $expr
     * @return Obj_Resolved
     */
    public static function resolve(PHPParser_Node_Expr $expr) {
        $return = new Helper_ExpressionResolver($expr);
        return $return->getResolvedObj();
    }
    
    
}


class VulnerabilityException extends Exception{}