<?php

class NodeVisitor_Exit extends PHPParser_NodeVisitorAbstract {
    public function leaveNode(PHPParser_Node $node) {
        // if node == exit or die().... exit scan process 
    }
}
?>
