<?php

class NodeVisitor_ConditionImportantExpression extends PHPParser_NodeVisitorAbstract {
    
    protected $returnNodes;
    
    public function beforeTraverse(array $nodes) {
        $this->returnNodes = array();
    }
    
    public function enterNode(PHPParser_Node $node) {
        // Function call
        if($node instanceof PHPParser_Node_Expr_FuncCall) {
            $this->returnNodes[] = $node;
        }
        // Assignment
        elseif(substr(get_class($node), 0, 26) == 'PHPParser_Node_Expr_Assign') {
            $this->returnNodes[] = $node;
        }
        // else { Todo:    If there is an LogicalOr and left expr is true, unset the right expression
    }
    
    public function getReturnNodes() {
        return $this->returnNodes;
    }
}
