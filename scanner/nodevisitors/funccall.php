<?php
/**
 * This visitor is used when a function call was found
 */
class NodeVisitor_FuncCall extends PHPParser_NodeVisitorAbstract {
    public function leaveNode(PHPParser_Node $node) {
        // XSS Function?
        if($ret = Attack_Xss::isXssFunction($node)) {
            Attack_Xss::checkNode($node, $ret);
        }
        //elseif...
        elseif($node instanceof PHPParser_Node_Expr_FuncCall) {
            return $this->handleFuncCall($node);
        }
    }
    
    protected function handleFuncCall(PHPParser_Node_Expr_FuncCall $node) {
        $resolve = Helper_ExpressionResolver::resolve($node);
        var_dump($resolve);
        
    }
}