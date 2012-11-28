<?php
/**
 * This class contains information about the scanning process, like which files
 * are checked, which scan-run are we in at the moment^, which functions, classes
 * and variables are known etc.
 * 
 * ^ If a function or method is called that is unknown at runtime but declared later,
 *   all files will be scanned again.
 * 
 */
abstract class Scanner {
    /////////////////////////////////////////////////////////////////////////
    /////// Properties
    ////////////////////////////////////////////////////////////////////////
    /**
     *
     * @var PHPParser_Parser 
     */
    protected static $parser;
    
    /**
     *
     * @var PHPParser_PrettyPrinter
     */
    protected static $printer;
    
    /**
     *
     * @var PHPParser_NodeTraverser
     */
    protected static $traverser;
    
    /**
     *
     * @var PHPParser_NodeDumper 
     */
    protected static $dumper;
    
    /**
     * Exportdata using for forks when control structures are reached.
     * @var array
     */
    protected static $exportdata;
    
    /**
     * Are we currently exiting the scanning process?
     * @var type 
     */
    protected static $state_exiting = false;
    
    /**
     * Scan ended but there might be paths left to go.
     * @var type 
     */
    protected static $state_endofscan = false;
    
    /**
     * Scan fully completed
     * @var type 
     */
    protected static $state_scancomplete = false;
    
    
    
    /////////////////////////////////////////////////////////////////////////
    /////// Methods
    ////////////////////////////////////////////////////////////////////////
    
    //--- Initialisation ------------------------------------------------------
    /**
     * Autoloading for scanner files and node visitors.
     * @param string $class_name
     */
    private static function scanner_autoload($class_name) {
        switch($class_name) {
            case 'Warning':
                require(dirname(__FILE__).'/definitions/warning.php');
                break;
            case 'Assignment':
                require(dirname(__FILE__).'/definitions/assignment.php');
                break;
            case 'Securing':
                require(dirname(__FILE__).'/definitions/securing.php');
                break;
            case 'Vulnerability':
                require(dirname(__FILE__).'/definitions/vulnerability.php');
                break;
            case 'PHPParser_NodeVisitorAbstract':
                require(dirname(__FILE__).'/../parser/PHPParser/NodeVisitorAbstract.php');
                break;
        }
        
        if(substr($class_name, 0, 12) == 'NodeVisitor_') {
            include(dirname(__FILE__).'/nodevisitors/'.strtolower(substr($class_name, 12)).'.php');
        }
        elseif(substr($class_name, 0, 7) == 'Attack_') {
            include(dirname(__FILE__).'/nodevisitors/attack/'.strtolower(substr($class_name, 7)).'.php');
        }
        elseif(substr($class_name, 0, 7) == 'Helper_') {
            include(dirname(__FILE__).'/helpers/'.strtolower(substr($class_name, 7)).'.php');
        }
        elseif(substr($class_name, 0, 4) == 'Obj_') {
            include(dirname(__FILE__).'/objects/'.strtolower(substr($class_name, 4)).'.php');
        }
    }
    
    /**
     * Register autoloader, initialise parser.
     */
    public static function init() {
        // -- autoloader
        spl_autoload_register(array("self", "scanner_autoload"));
        include(dirname(__FILE__).'/function_replacements/functions.php');
        
        // -- parser
        self::$parser   = new PHPParser_Parser(new PHPParser_Lexer);
        self::$printer  = new PHPParser_PrettyPrinter_Zend;
        self::$dumper   = new PHPParser_NodeDumper;
        
        $traverser = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new NodeVisitor_FileAttributeSetter);
        $traverser->addVisitor(new NodeVisitor_QualifiedNameResolver);
        $traverser->addVisitor(new NodeVisitor_Scope);
        $traverser->addVisitor(new NodeVisitor_Include);
        //$traverser->addVisitor(new NodeVisitor_Function);
        $traverser->addVisitor(new NodeVisitor_FuncCall);
        $traverser->addVisitor(new NodeVisitor_Exit);
        $traverser->addVisitor(new NodeVisitor_Assignments);
        $traverser->addVisitor(new NodeVisitor_ControlStructure);
        
        self::$traverser = $traverser;
        
        self::registerSubscribers();
               
        // initialise scan information
        ScanInfo::init();
    }
    
    private static function registerSubscribers() {
        /**
         * File got scanned to the end. If we're in "EXITING-State" do nothing,
         * else reactivate all NodeVisitors.
         */
        subscribe('endScanFile', function() {
            self::$traverser->hasFinished(false);
            
            if(!self::$state_exiting) {
                // set all visitors active again
                self::$traverser->setActiveVisitors(array());
            }
        });
        
        
        // End of this scanning iteration reached, check if there are controlstructure paths left
        subscribe('endOfRun', function() {
            $control_structs = &ScanInfo::getControlStructures();
            if(!empty($control_structs)) {
                // get last control structure
                /* @var $struct Obj_Controlstructure */
                $struct = null;
                while($struct == null) {
                    $struct = end($control_structs);
                    // if last path of structure has been gone in this iteration, delete this
                    // controlstructure and go to next higher level structure
                    if($struct->getCurrentPathNumber() == $struct->getNumPaths()-1) {
                        ScanInfo::removeLastControlStructure();
                        $struct = null;
                    }
                    else {
                        $struct->incCurrentPathNumber();
                    }
                    // no structures anymore
                    if(empty($control_structs)) {
                        break;
                    }
                }

                if($struct) {
                    $skipwalker = new NodeVisitor_WalkTo($struct->getNode(), $struct->getFileStack());
                    $tree = ScanInfo::getFileTree();
                    $file = $tree::getMainFile()->getPath();
                    // start scanning from beginning with NodeVisitor_WalkTo enabled -> walks to last found controlstructure
                    Scanner::walkFile($file, $skipwalker);
                }
            }
        });
    }
    
    
    // --- Scanning methods ---------------------------------------------------------------
    /**
     * Get the real location of the file that shall be included.
     * This method uses get_include_path() to detect the file using the include paths
     * @param string $file
     * @return string|false
     */
    protected static function getFileLocation($file) {
        if(!is_string($file)) return false;
        
        $include_paths = explode(PATH_SEPARATOR, get_include_path());
        
        $filetree = ScanInfo::getFileTree();
        if($filetree) {
            chdir(dirname($filetree->getPath()));
        }
        
        $file_path = DIRECTORY_SEPARATOR. $file;
        
        foreach($include_paths as $inc_path) {
            $full_path = realpath($inc_path.$file_path);
            if(file_exists($full_path)) {
                return $full_path;
            }
            
        }
        
        // file not found
        return false;
    }
    
    /**
     * Does not scan the file if it has been scanned already. Needed for include_once()
     * and require_once().
     * @param type $rel_file
     * @param type $node
     */
    public static function scanFileOnce($rel_file, $node) {
        //$filetree = ScanInfo::getFileTree();
        
    }
    
    /**
     * Start scanning a new file. This method:
     *  - detects the full path to the file tree
     *  - writes the new file to the file tree
     *  - parses the file
     *  - traverses the nodes
     * @param string $rel_file File to be included
     * @param PHPParser_Node $node Node of the include or require
     */
    public static function scanFile($rel_file, $node=null) {
        // get real file path
        if(!$file = self::getFileLocation($rel_file)) {
            ScanInfo::addFailedInclude(new Obj_FailedInclude($node)  );
            return false;
        }
        
        // add file to filetree
        ScanInfo::addFile($file, $node);
        
        $parser = self::$parser;
        $traverser = self::$traverser;
        $nodeDumper = self::$dumper;

        // execute parsing and traversing
        try {
            // parse
            fire('beginParseFile', array($file));
            $stmts = $parser->parse(ScanInfo::getCurrentFileContent());
            fire('endParseFile', array($file, $stmts));
            
            
            // just dump the parsed file
            if(SCANNER_DUMP_TREE) {
                echo ($nodeDumper->dump($stmts))."\n\n\n";
            }
            else {
                // Scan File
                fire('beginScanFile', array($file));
                $traverser->traverse($stmts);
                fire('endScanFile', array($file));
                
                echo ($nodeDumper->dump($stmts))."\n\n\n";
            }
        } 
        catch (PHPParser_Error $e) {
            fire('parseError', array($file, $e->getMessage()));
        }
        
        fire('endOfRun');
    }
    
    /**
     * This method gets called when return, exit or die get called in the function
     */
    public static function scanFileEnd($exit=false) {
        // Has not finished yet ("return", "die" or "exit" called) 
        if(!self::$traverser->hasFinished()) {
            
            // just set fileattributesetter as active vistor -> don't detect any vulnerabilities
            self::$traverser->setActiveVisitors(array(0));
            self::$state_exiting = $exit ? true : false;
            
        }
            
    }
    
    
    
    /**
     * Walk to a given node in the file without scanning the nodes until this node is set
     * @param string $file Path to the file that gets scanned
     * @param NodeVisitor_WalkTo From which node the scanning process shall be started.
     */
    public static function walkFile($file, NodeVisitor_WalkTo $nodevisitor) {
        // TODO: chmod?
        $parser    = self::$parser;
        $traverser = self::$traverser;
        $idx = $traverser->addVisitor($nodevisitor);
        $traverser->setActiveVisitors(array(0,1,$idx));

        // execute parsing and traversing
        try {
            // parse
            $stmts = $parser->parse(file_get_contents($file));
            
            // traverse
            $traverser->traverse($stmts);
        } catch (PHPParser_Error $e) {
            echo 'Parse Error: ', $e->getMessage();
        }
        fire('endOfRun');
    }
    
    /**
     * This method gets called after the node we walked to was found. Deletes all NodeVisitor_WalkTo
     * and re-enable all nodevisitors.
     */
    public static function walkFileEnd() {
        $traverser = self::$traverser;
        $traverser->removeVisitor('NodeVisitor_WalkTo');
        $traverser->setActiveVisitors(array());
    }
    
    /**
     * Scan a given set of statements, i.e. to analyse functions or classes
     * @param string $identifier
     * @param array $stmts
     */
    public static function scanStatements($identifier, array $stmts) {
        
    }
    
    public static function scanStatementsEnd() {
        
    }
    
    
    // ---------------- Other methods/ helpers -----------------------------
    /**
     * Converts a PHParser_Node into prettified PHP code.
     * @param PHPParser_Node_Expr $node
     * @return string
     */
    public static function prettyPrintExpr(PHPParser_Node_Expr $node) {
        return self::$printer->prettyPrintExpr($node);
    }
    
    public static function printNode(PHPParser_Node $node) {
        return self::$printer->p($node);
    }

    /**
    * Returns a securified relative url path. File protocols and back paths (../) get removed.
    * @param type $url
    * @return type
    */
    public static function secureurl($url) {
        $i = 0;
        // remove chars that shouldnt be in a url
        $path = preg_replace('#[^[:alnum:]äöüß_+? .%/!\#()\-]#iu', '', $url);
        // remove http:// and ../
        while (($tmp = preg_replace('/(^|\/)((https?|ftp|mailto):\/\/|\.\.\/|\.\/)/isu', '/', $path)) != $path)
            $path = $tmp;
        while (@($path[$i++] == '/'));
        return trim(substr($path, ($i - 1)));
    }
}