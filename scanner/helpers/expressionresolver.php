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
        $resolved->setSafeReturn(false);
        $resolved->setUserDefined(false);
        
        if($expr instanceof PHPParser_Node_Scalar) {
            $this->resolveScalar($expr);
        }
        // => EXPR_VARIABLE          $variable
        elseif($expr instanceof PHPParser_Node_Expr_Variable) {
            $this->resolveExprVariable($expr);
        }
        // => EXPR_ARRAYDIMFETCH     $array[$dimension]
        elseif($expr instanceof PHPParser_Node_Expr_ArrayDimFetch) {
            $this->resolveExprArrayDimFetch($expr);
        }
        elseif($expr instanceof PHPParser_Node_Expr_Concat) {
            $this->resolveExprConcat($expr);
        }
        elseif($expr instanceof PHPParser_Node_Expr_FuncCall) {
            $this->resolveExprFunctionCall($expr);
        }
        elseif($expr instanceof PHPParser_Node_Arg) {
            $this->resolveArg($expr);
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
        $this->obj_resolved->setSafeReturn(true);
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
                $this->obj_resolved->setSecuredFor($variable->getSecuredFor());
                $this->obj_resolved->setValue($variable->getValue());
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
     * Resolve concatenations.
     * @param PHPParser_Node_Expr $expr
     */
    protected function resolveExprConcat(PHPParser_Node_Expr_Concat $expr) {
        $left  = Helper_ExpressionResolver::resolve($expr->left);
        $right = Helper_ExpressionResolver::resolve($expr->right);
        
        $this->obj_resolved->setExecutable($left->isExecutable() && $right->isExecutable());
        $this->obj_resolved->setSafeReturn($left->getSafeReturn() && $right->getSafeReturn());
        #$resolved->setSecuredFor(array());
        $this->obj_resolved->setUserDefined($left->isUserDefined() || $right->isUserDefined());
        
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
    
    
    protected function resolveExprConcatPrepareResolvedForEval(Obj_Resolved $resolved) {
        if($resolved->getvalue() !== null) { 
            return "'".$resolved->getValue()."'";
        }
        else {
            return Scanner::prettyPrintExpr($resolved->getExpression());
        }
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
            $this->obj_resolved->setSecuredFor($variable->getSecuredFor());
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
            $this->obj_resolved->setValue(null);
            return;
        }
        
        // if it is a system function: execute it if it's safe, otherwise generate a random value
        if(!$func->isUserDefined()) {
            if($func->isExecutable()) {
                $values     = array();
                $references = array();
                $error  = false;
                
                foreach($expr->args as $arg) {
                    /*@var $val Obj_Resolved*/
                    $val = self::resolve($arg->value);
                    if($val->isResolveError()) {
                        $error = true;
                    }
                    else {
                        $values[]     = $val->getValue();
                        $references[] = $arg->byRef; 
                    }
                }
                if(!$error) {
                    // Todo: Fine Tuning ^^
                    $this->obj_resolved->setValue(call_user_func_array($name, $values));
                }
            }
        }
    }
    
    /**
     * 
     * @param PHPParser_Node_Arg $arg
     */
    protected function resolveArg(PHPParser_Node_Arg $arg) {
        
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