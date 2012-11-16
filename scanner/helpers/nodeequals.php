<?php

/**
 * Checks if a node equals another node 
 */

abstract class Helper_NodeEquals {
    public static function equals(PHPParser_Node $node1, PHPParser_Node $node2) {
        if(get_class($node1) != get_class($node2)) {
            return false;
        }
        
        $node1_attr = $node1->getAttributes();
        $node2_attr = $node2->getAttributes();
        
        
        
        if(   $node1->getAttribute("file")   == $node2->getAttribute("file")
           && $node1->getLine() == $node2->getLine()
           && count($node1->getSubNodeNames()) == count($node2->getSubNodeNames()))
        {
            return true;
        }
        return false;
    }
}
?>
