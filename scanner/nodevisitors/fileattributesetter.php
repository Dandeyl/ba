<?php

class NodeVisitor_FileAttributeSetter extends PHPParser_NodeVisitorAbstract {
    function enterNode(PHPParser_Node $node) {
        // set line
        ScanInfo::setCurrentFileLine($node->getLine());
        
        // set file for node
        $node->setAttribute('file', ScanInfo::getCurrentFilePath());
    }
    
    function afterTraverse(array $nodes) {
        // End of this file
        Scanner::scanFileEnd();
    }
}