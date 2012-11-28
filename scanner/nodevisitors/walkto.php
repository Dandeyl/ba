<?php
/**
 * Walks to a given node and skips all other nodes.
 * Takes care of includes and requires.
 */
class NodeVisitor_WalkTo extends PHPParser_NodeVisitorAbstract {
    /**
     * The node to walk to
     * @var type 
     */
    protected $node;
    
    /**
     * The files that were included to get to that node 
     * @var Obj_FileTree[]
     */
    protected $include_files;
    
    /**
     * File that has to be included next
     * @var Obj_Filetree 
     */
    protected $next_include_file;
    
    /**
     * Has the node been found.
     * @var bool $node_found Has The node been found
     */
    protected $node_found;
    
    public function __construct($node=null, array $include_files=null) {
        $this->next_include_file = null;
        $this->include_files = array();
        
        if($node === null) { $this->node_found = true; return; }
        
        $this->node = $node;
        if(!empty($include_files)) {
            $this->include_files = $include_files;
            $this->next_include_file = array_shift($this->include_files);
        }
    }
    
    public function enterNode(PHPParser_Node $node) {
        if($this->node_found === true) return;
        
        // no include file left and NODE FOUND!
        if((null == $this->next_include_file)
           && Helper_NodeEquals::equals($node, $this->node)) {
            $this->node_found = true;
            Scanner::walkFileEnd();
            return;
        }
        
        // include or require found
        elseif(null !== $this->next_include_file
               && $node instanceof PHPParser_Node_Expr_Include)
        {
            // Include node to get to the node we're looking for was found
            if(Helper_NodeEquals::equals($node, $this->next_include_file->call_node)) {
                $this->next_include_file->setCurrent();
                $file = $this->next_include_file->path;
                $this->next_include_file = array_shift($this->include_files);
                Scanner::walkFile($file, $this);
            }
        }
        
        // controlstructure found
        elseif(NodeVisitor_ControlStructure::isControlStructure($node)) {
            $struct = ScanInfo::findControlStructureByNode($node);
            return $struct->getPath($struct->getCurrentPathNumber())->getStmts();
        }
        
        return;
    }
}