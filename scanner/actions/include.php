<?php
/**
 * Detects file inclusions and tries to add those files to the file tree.
 */
class Action_Include {
    /**
     * If the include expression could not be read directly but the expression
     * can be safely executed, execute it to get the include path.
     * @param PHPParser_Node $node
     */
    public function leaveNode(PHPParser_Node_Expr_FuncCallInclude $node) {
        // execute expression
        $resolve = Helper_ExpressionResolver::resolve($node->args[0]->value);
        $file = $resolve->getValue();

        if($file) {
            Scanner::scanFile($file, $node);
        }
        else {
            Scanner::scanFile(false, $node);
        }
    }
    
    
}