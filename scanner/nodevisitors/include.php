<?php
/**
 * Detects file inclusions and tries to add those files to the file tree.
 */
class NodeVisitor_Include extends PHPParser_NodeVisitorAbstract {
    /**
     * If the include expression could not be read directly but the expression
     * can be safely executed, execute it to get the include path.
     * @param PHPParser_Node $node
     */
    public function leaveNode(PHPParser_Node $node) {
        //$expression_info = Scanner::stopObserveNode($node);
        
        if($node instanceof PHPParser_Node_Expr_Include) {
            // execute expression
            $resolve = Helper_ExpressionResolver::resolve($node->expr);
            $file = $resolve->getValue();
            
            if($file) {
                Scanner::scanFile($file, $node);
            }
            else {
                Scanner::scanFile(false, $node);
            }
        }
    }
    
    
}