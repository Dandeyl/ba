<?php
/**
 * Twig is the class needed to build the filetree. The main file is the first twig
 */
class Obj_Filetree {
    protected $path;
    protected $line;
    protected $_twigs;
    protected $_pointer = -1;
    /**
     ** @var PHPParser_Node Node this file got called in. 
     */
    protected $call_node;
    
            
    /**
     * Twig we started with.
     * @var Obj_Filetree
     */
    protected static $maintwig;
    
    /**
     * Array that indicates where in the filetree we are at the moment.
     * @var array
     */
    protected static $levels = array();
    
    /**
     * Pointer to current twig
     * @param &Filetree
     */
    protected static $current;
    
    
    public function __construct($path, $node = null) {
        if(!file_exists($path)) {
            throw new Exception("File does not exist: ".$path.".\n");
        }
        
        // set starting twig
        if(!isset(self::$maintwig)) {
            self::$maintwig = &$this;            
            self::$current  = &$this;
        }
        
        $this->path  = $path;
        $this->call_node = $node; 
        $this->_twigs = array();
        $this->_pointer = -1;
        $this->_controlstructs = array();
    }
    
    /**
     * Adds a new named twig and enters it.
     */
    public function push($name, $node) {
        $index = (++self::$current->_pointer);
        self::$current->_twigs[$index] = new Obj_Filetree($name, $node);
        
        // enter current twig (because that's the file that has to be scanned now)
        self::$levels[] = $index;
        self::$current = &self::$current->_twigs[$index];
    }
    
    /**
     * Return from the current twig to the parent twig.
     */
    public function parent() {
        if(empty(self::$levels)) {
            throw new Obj_Filetree_EndOfTree_Exception("End of Scan reach'd :)");
        }
        
        array_pop(self::$levels);
        
        // start with main twig, then enter subtwigs
        self::$current = &self::$maintwig; 
        foreach(self::$levels as $lev) {
            self::$current = &self::$current->_twigs[$lev];
        }
    }
    
    /**
     * Returns the path of the current file.
     * @return string
     */
    public function getPath() {
        return self::$current->path;
    }
    
    /**
     * Get content of current file.
     */
    public function getContents() {
        return file_get_contents(self::$current->path);
    }
    
    /**
     * Change the scanning line of the current file
     * @param type $line
     * @throws Exception
     */
    public function setLine($line) {
        if(!is_int($line)) {
            throw new Exception("Filetree->setLine: Invalid argument: ".var_export($line, true));
        }
        
        $this->line = $line;
    }
    
    public function getLine() {
        return (int) $this->line;
    }
    
    /**
     * Return the node in which the current file got included
     */
    public function getIncludeNode() {
        return self::$current->call_node;
    }
    
    
    
    /**
     * Return the stack that was created to get to this file. Return:
     * array(
     *     Obj_FileTree $f1, // this file was included in the main file 
     *     Obj_FileTree $f2  // this file was include in $f1.
     * )
     * @return array
     */
    public function getCurrentFileStack() {
        $return = array();
        $twig = self::$maintwig;
        
        foreach(self::$levels as $lvl) {
            $twig = $twig->_twigs[$lvl];
            if($twig) {
                $return[] = $twig;
            }
        }
        
        return $return;
    }
    
    public static function getMainFile() {
        return self::$maintwig;
    }
}

/**
 * Gets thrown when all files got scanned to the end.
 */
class Obj_Filetree_EndOfTree_Exception extends Exception {}