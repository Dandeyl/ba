<?php
/**
 * This Nodevisitor detects possible xss vulnerabilities. Functions that can cause xss atacks are:
 * 
 * FUNCTION      ARGUMENT-THAT-IS-VULN    PARSER-NODE    
 * echo            all                    PHPParser_Node_Stmt_Echo
 * print           1st                    PHPParser_Node_Expr_Print
 * printf          all                    PHPParser_Node_Expr_FuncCall (->name == "printf" (PHPParser_Node_Name))
 * vprintf         all                    PHPParser_Node_Expr_FuncCall (->name == "vprintf" (PHPParser_Node_Name))
 * exit            1st                    PHPParser_Node_Expr_Exit   (has ->expr property)
 * die             1st                    PHPParser_Node_Expr_Exit
 * print_r         1st                    PHPParser_Node_Expr_FuncCall (->name == "print_r") 
 */


class Attack_SqlInjection
{

    

   /**
     * Checks if the passed node is a function or language construct, that is xss vulnerable
     * @param PHPParser_Node $node
     * @return int||false The arguments that have to be checked for user input.\
     *         If an empty array gets returned all arguments have to be checked, 
     */
    public static function isSqlInjectableFunction(PHPParser_Node $node) {
        if($node instanceof PHPParser_Node_Expr_FuncCall) {
            $name = Helper_NameResolver::resolve($node);
            $func = ScanInfo::findFunction($name);
            
            if($func) {
                // check if function is vulnerable
                if($func->isVulnerableFor(Attack::SqlInjection)) {
                    return $func;
                }
            }
        }
        
        // node is no xss vulnerable function
        return false;
    }
    
    
    public static function checkNode(PHPParser_Node $node, $function) {
        // vulnerable function or statement found
        if($function instanceof Obj_Function) {
            self::checkFunction($function, $node);
        }
    }
    
    
    /**
     * Checks all expressions following the echo language construct
     * @param PHPParser_Node $node
     */
    public static function checkFunction(Obj_Function $function, PHPParser_Node $node) {
        $vuln_arguments = $function->getVulnerableParameters();
        
        foreach($node->args as $key => $arg) {
            if(!empty($vuln_arguments) && !in_array(($key+1), $vuln_arguments)) {
                continue;
            }
            $resolved = Helper_ExpressionResolver::resolve($arg->value);
            if(self::checkSqlInjectionCondition($resolved)) {
                ScanInfo::addVulnerability(Attack::SqlInjection, $node);
                break;
            }
        }
    }
    
    
    
    /**
     * Return if all conditions required for a possible xss condition are met
     * @param Obj_Resolved $resolved
     */
    public static function checkSqlInjectionCondition(Obj_Resolved $resolved) {
        if($resolved->isUserDefined() 
           && !(   $resolved->isSecuredBy(Securing::AddSlashes)
                || $resolved->isSecuredBy(Securing::Base64Encode))
           && !in_array($resolved->getReturnType(), array('bool', 'integer', 'float'))
        )  {
            return true;
        }
        
        return false;
    }
}