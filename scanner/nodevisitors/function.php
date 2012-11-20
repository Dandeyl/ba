<?php
/**
 * This nodevisitor detects definitions of functions and methods, analyses them and adds them to the global function list.
 * 
 */
class NodeVisitor_Function extends PHPParser_NodeVisitorAbstract {
    public function leaveNode(PHPParser_Node $node) {
        if(!($node instanceof PHPParser_Node_Stmt_Function)) {
            return;
        }
        
        $function = new Obj_Function();
        $function->setName($node->name);
        $function->setReturnByRef($node->byRef);
        $function->setStatements($node->stmts);
        $function->setParameters($node->params);
        
        // start analysing the function
        
    }
}