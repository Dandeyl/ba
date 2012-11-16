<?php

class NodeVisitor_Unset extends PHPParser_NodeVisitorAbstract {
    public function enterNode(PHPParser_Node $node) {
        if(!($node instanceof PHPParser_Node_Stmt_Unset)) {
            return;
        }
        
        foreach($node->vars as $var) {
            $name = Helper_NameResolver::resolve($var);
            if($name) {
                ScanInfo::removeVar($name);
            }
        }
    }
}