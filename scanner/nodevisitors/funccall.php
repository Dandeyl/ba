<?php
/**
 * This visitor is used when a function call was found
 */
class NodeVisitor_FuncCall extends PHPParser_NodeVisitorAbstract {
    public function leaveNode(PHPParser_Node $node) {
        if(!($node instanceof PHPParser_Node_Expr_FuncCall)
           && !($node instanceof PHPParser_Node_Stmt_Echo)
           && !($node instanceof PHPParser_Node_Expr_Print)
           && !($node instanceof PHPParser_Node_Expr_ShellExec)) {
            return;
        }
        
        // XSS Function?
        if($ret = Attack_Xss::isXssFunction($node)) {
            Attack_Xss::checkNode($node, $ret);
        }
        //elseif...
        
        if($node instanceof PHPParser_Node_Expr) {
            $resolve = Helper_ExpressionResolver::resolve($node);
            return $resolve->getExpression();
        }
        
    }
    
    protected function handleFuncCall(PHPParser_Node_Expr_FuncCall $node) {
        $name = Helper_NameResolver::resolve($node);
        $func = ScanInfo::findFunction($name);
        
        if(!$func) {
            ScanInfo::addNotFoundFunction($name);
            return;
        }
        
        if($func->isUserDefined()) {
            // Set scope
            ScanInfo::setScope('Function#'.$name);
            
            // register parameters
            $params = $func->getParameters();
            
            // clear all parameters
        }
        
    }
}