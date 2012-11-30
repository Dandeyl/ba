<?php

class NodeVisitor_ActionChooser extends PHPParser_NodeVisitorAbstract {
    protected $actions;
    
    public function __construct() {
        $this->actions["controlstructure"] = new Action_ControlStructure(); 
        $this->actions["exit"] = new Action_Exit(); 
        $this->actions["include"] = new Action_Include(); 
        $this->actions["function"] = new Action_Function(); 
        $this->actions["funccall"] = new Action_FuncCall(); 
        $this->actions["assignments"] = new Action_Assignments(); 
    }
    
    public function enterNode(PHPParser_Node $node) {
        if(Scanner::isExiting()) return;
        if(Scanner::isWalking()) return;
        
        
        // if a controlstructure is found:
        //  check if it is registered. no: register it and follow the first path
        //                            yes: followe the current path
        if(Action_ControlStructure::isControlStructure($node)) {
            return $this->actions["controlstructure"]->enterNode($node);
        }
        // if function definition is found: save statements, register function and replace the current node
        elseif($node instanceof PHPParser_Node_Stmt_Function) {
            return $this->actions['function']->enterNode($node);
        }
    }
    
    
    public function leaveNode(PHPParser_Node $node) {
        if(Scanner::isExiting()) return;
        if(Scanner::isWalking()) return;
        
        // Handle expression
        if($node instanceof PHPParser_Node_Expr) {
            Helper_ExpressionResolver::resolve($node);
        }
        
        // Other actions
        if($node instanceof PHPParser_Node_Expr_FuncCallExit) {
            return $this->actions["exit"]->leaveNode($node);
        }
        elseif($node instanceof PHPParser_Node_Expr_FuncCallInclude) {
            return $this->actions["include"]->leaveNode($node);
        }
    }
}