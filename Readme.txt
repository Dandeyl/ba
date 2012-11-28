TESTprojekt

##################################################
## PHP Source Code Scanner Vulnerability Finder  #
##################################################

Proteus parses php files and scans the nodes for security vulnerabilities.

Scanning on each node includes:
   - detect scope changes
   - include and require files
   - evaluate classes, methods and functions
   - evaluate global code

usually everything is done in one scanning step. However, if a method or function
gets called that is unknown and later during the scanning process this function
is found, Proteus will scan all files another time.

#### How the scanning process works: ###########
 
##### Events fired ######
parseError     - parse error occurred
beginParseFile - start parsing a file
endParseFile   - end parsing a file
beginScanFile  - start scanning the nodes of a file
endScanFile    - end scanning the nodes of a file
endOfRun       - scanning ended but there might be paths left
endOfScan      - scannning ended completely



#### What has to be taken care of: ###########
Variables:
   Variables defined in classes and functions are not visible from outside
   Dynamic variables: $$var
   References: &$var
   References can be set, even if the variable is not yet defined
   If a variable is unset, all reference will still hold its value
   
 Funktionen:
   Können beliebig viele Parameter haben  

 If/Switch/While/For/Foreach/Do:
   - vor dem Betreten eine Sicherung der Scanner-Daten machen
   - 
   - kann bei weiteren iterationen übersprungen werden wenn: 
         im stmt ein die oder exit vorkommt
         keine variablenzuweisung gemacht wird


#### What has to be done in future releases: #################
Variables:
    - split the global list of variables into many smaller ones -> Performance
    - if a variable is unset, all references will still hold its value

#### Executable functions ##################
Executable functions are functions that can be executed without changing
the environment. List of executable functions:


addcslashes
addslashes
bin2hex
chop
chr
chunk_split
convert_cyr_string
convert_uudecode
convert_uuencode
count_chars
crc32
crypt
explode
get_html_translation_table
hebrev
hebrevc
hex2bin
html_entity_decode
htmlentities
htmlspecialchars_decode
htmlspecialchars
implode
join
lcfirst
levenshtein
localeconv
ltrim
md5_file
md5
metaphone
money_format
nl_langinfo
nl2br
number_format
ord
parse_str
quoted_printable_decode
quoted_printable_encode
quotemeta
rtrim
setlocale
sha1_file
sha1
similar_text
soundex
sprintf
sscanf
str_getcsv
str_ireplace
str_pad
str_repeat
str_replace
str_rot13
str_shuffle
str_split
str_word_count
strcasecmp
strchr
strcmp
strcoll
strcspn
strip_tags
stripcslashes
stripos
stripslashes
stristr
strlen
strnatcasecmp
strnatcmp
strncasecmp
strncmp
strpbrk
strpos
strrchr
strrev
strripos
strrpos
strspn
strstr
strtok
strtolower
strtoupper
strtr
substr_compare
substr_count
substr_replace
substr
trim
ucfirst
ucwords
vfprintf
vprintf
vsprintf
wordwrap

//////////////////

checkdate
date_add
date_create_from_format
date_create
date_date_set
date_default_timezone_get
date_default_timezone_set
date_diff
date_format
date_get_last_errors
date_interval_create_from_date_string
date_interval_format
date_isodate_set
date_modify
date_offset_get
date_parse_from_format
date_parse
date_sub
date_sun_info
date_sunrise
date_sunset
date_time_set
date_timestamp_get
date_timestamp_set
date_timezone_get
date_timezone_set
date
getdate
gettimeofday
gmdate
gmmktime
gmstrftime
idate
localtime
microtime
mktime
strftime
strptime
strtotime
time
timezone_abbreviations_list
timezone_identifiers_list
timezone_location_get
timezone_name_from_abbr
timezone_name_get
timezone_offset_get
timezone_open
timezone_transitions_get
timezone_version_get

////////////////

array_ change_ key_ case
array_ chunk
array_ combine
array_ count_ values
array_ diff_ assoc
array_ diff_ key
array_ diff_ uassoc
array_ diff_ ukey
array_ diff
array_ fill_ keys
array_ fill
array_ filter
array_ flip
array_ intersect_ assoc
array_ intersect_ key
array_ intersect_ uassoc
array_ intersect_ ukey
array_ intersect
array_ key_ exists
array_ keys
array_ map
array_ merge_ recursive
array_ merge
array_ multisort
array_ pad
array_ pop
array_ product
array_ push
array_ rand
array_ reduce
array_ replace_ recursive
array_ replace
array_ reverse
array_ search
array_ shift
array_ slice
array_ splice
array_ sum
array_ udiff_ assoc
array_ udiff_ uassoc
array_ udiff
array_ uintersect_ assoc
array_ uintersect_ uassoc
array_ uintersect
array_ unique
array_ unshift
array_ values
array_ walk_ recursive
array_ walk
array
arsort
asort
compact
count
current
each
end
extract
in_ array
key
krsort
ksort
list
natcasesort
natsort
next
pos
prev
range
reset
rsort
shuffle
sizeof
sort
uasort
uksort
usort






#### Node List ##############################

Node_Arg

Node_Const

Node_Expr
    Array
    ArrayDimFetch
    ArrayItem
    Assign
Node_Name

Node_Param

Node_Scalar

Node_Stmt