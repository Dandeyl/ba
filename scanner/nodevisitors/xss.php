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


class NodeVisitor_Xss extends PHPParser_NodeVisitorAbstract
{
    const STMT_ECHO  = 1;
    const EXPR_PRINT = 2;
    const EXPR_EXIT  = 3;
    const EXPR_FUNC  = 4;

   /**
     * Checks if the passed node is a function or language construct, that is xss vulnerable
     * @param PHPParser_Node $node
     * @return int||false The arguments that have to be checked for user input.\
     *         If an empty array gets returned all arguments have to be checked, 
     */
    protected function isXssFunction(PHPParser_Node $node) {
        // echo
        if($node instanceof PHPParser_Node_Stmt_Echo) {
            // all arguments are vulnerable
            return self::STMT_ECHO;
        }
        
        // print
        if($node instanceof PHPParser_Node_Expr_Print) {
            // return that only the first argument is vulnerable
            return self::EXPR_PRINT;
        }
        
        // exit or die
        if($node instanceof PHPParser_Node_Expr_Exit) {
            // return that only the first argument is vulnerable
            return self::EXPR_EXIT;
        }
        
        // printf, vprintf, print_r and user defined functions
        if($node instanceof PHPParser_Node_Expr_FuncCall) {
            $name = Helper_NameResolver::resolve($node);
            $func = false;//ScanInfo::findFunction($name);
            
            if($func) {
                // check if function is vulnerable
                if($func->isVulnerable('xss')) {
                    return $func;
                }
            }
        }
        
        // node is no xss vulnerable function
        return false;
    }
    
    
    public function leaveNode(PHPParser_Node $node) {
        $vulnerable = $this->isXssFunction($node);
        
        // no vulnerable node, do nothing
        if($vulnerable === false) {
            return null;
        }
        
        // vulnerable function or statement found
        switch($vulnerable) {
            case self::STMT_ECHO:
                $this->checkEcho($node);
                break;
            case self::EXPR_PRINT:
                $this->checkPrint($node);
                break;
            case self::EXPR_EXIT:
                $this->checkExit($node);
                break;
            default: // function
                if($vulnerable instanceof Obj_Function) {
                    $this->checkFunction($vulnerable, $node);
                }
                break;
        }
    }
    
    
    /**
     * Checks all expressions following the echo language construct
     * @param PHPParser_Node $node
     */
    public function checkEcho(PHPParser_Node $node) {
        foreach($node->exprs as $expr) {
            $resolved = Helper_ExpressionResolver::resolve($expr);
            if($resolved->isUserDefined() && !$resolved->isSecuredFor('xss')) {
                ScanInfo::addVulnerability('xss', $node, ScanInfo::getCurrentFilePath(), ScanInfo::getCurrentFileLine());
                break;
            }
        }
    }
    
    /**
     * Checks the expression following the print language construct
     * @param PHPParser_Node $node
     */
    public function checkPrint(PHPParser_Node $node) {
        $resolved = Helper_ExpressionResolver::resolve($node->expr);
        
        if($resolved->isUserDefined() && !$resolved->isSecuredFor('xss')) {
            ScanInfo::addVulnerability('xss', $node, ScanInfo::getCurrentFilePath(), ScanInfo::getCurrentFilePath());
        }
    }
}