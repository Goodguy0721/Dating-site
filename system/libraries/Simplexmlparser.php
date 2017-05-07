<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
//!-----------------------------------------------------------------
// @class      SimpleXmlParser
// @desc       Cria um parser que constr�i uma estrutura de dados
//             a partir de um arquivo XML
// @author     Marcos Pont
//!-----------------------------------------------------------------
class CI_SimpleXmlParser
{
     public $root;                    // @var root    (object)       Objecto XmlNode raiz da �rvore XML
     public $parser;                  // @var parser  (resource)     Objeto xml_parser criado
     public $data;                    // @var data    (string)       Dados XML a serem interpretados pelo parser
     public $vals;                    // @var vals    (array)        Vetor de valores capturados do arquivo XML
     public $index;                   // @var index   (array)        Vetor de �ndices da �rvore XML
     public $charset = "UTF-8";  // @var charset (string)       Conjunto de caracteres definido para a cria��o do parser XML

     //!-----------------------------------------------------------------
     // @function        SimpleXmlParser::SimpleXmlParser
     // @desc            Construtor do XML Parser. Parseia o conte�do XML.
     // @access          public
     // @param           fileName  (string)       Nome do arquivo XML a ser processado
     // @param           data      (string)       Dados XML, se fileName = ""
     //!-----------------------------------------------------------------
     public function __construct()
     {
     }
    public function processFile($fileName = '', $data = '', $charset = '')
    {
        if ($data == "") {
            //if (!file_exists($fileName)) $this->_raiseError("Can't open file ".$fileName);
               $this->data = implode("", file($fileName));
        } else {
            $this->data = $data;
        }
        $this->data = eregi_replace(">" . "[[:space:]]+" . "<", "><", $this->data);
        $this->charset = ($charset != '') ? $charset : $this->charset;
        $this->_parseFile($fileName);
    }
    public function getXmlContent($folder)
    {
        $xml_parser = $this->processFile($folder);
        $xml_root = $this->getRoot();

        foreach ($xml_root->children as $cnt => $node) {
            $content[$node->attrs["name"]] = $node->value;
        }

        return $content;
    }
     //!-----------------------------------------------------------------
     // @function        SimpleXmlParser::getRoot
     // @desc            Retorna a raiz da �rvore XML criada pelo parser
     // @access          public
     // @returns         Raiz da �rvore XML
     //!-----------------------------------------------------------------
     public function getRoot()
     {
         return $this->root;
     }

     //!-----------------------------------------------------------------
     // @function        SimpleXmlParser::_parseFile
     // @desc            Inicializa o parser XML, setando suas op��es de
     //                  configura��o e executa a fun��o de interpreta��o
     //                  do parser armazenando os resultados em uma estrutura
     //                  de �rvore
     // @access          private
     //!-----------------------------------------------------------------
     public function _parseFile($fileName = "")
     {
         $this->parser = xml_parser_create($this->charset);
         xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, $this->charset);
         xml_parser_set_option($this->parser, XML_OPTION_SKIP_WHITE, 1);
         xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);

         if (!xml_parse_into_struct($this->parser, $this->data, $this->vals, $this->index)) {
             $this->_raiseError("Error while parsing XML File <b>$fileName</b> : " . xml_error_string(xml_get_error_code($this->parser)) . " at line " . xml_get_current_line_number($this->parser));
         }
         xml_parser_free($this->parser);
         $this->_buildRoot(0);
     }

     //!-----------------------------------------------------------------
     // @function        SimpleXmlParser::_buildRoot
     // @desc            Cria o apontador da raiz da �rvore XML a partir
     //                  do primeiro valor do vetor $this->vals. Inicia a
     //                  execu��o recursiva para montagem da �rvore
     // @access          private
     // @see             PHP2Go::_getChildren
     //!-----------------------------------------------------------------
     public function _buildRoot()
     {
         $i = 0;
         $this->root = new XmlNode($this->vals[$i]['tag'],
                                     (isset($this->vals[$i]['attributes'])) ? $this->vals[$i]['attributes'] : null,
                                       $this->_getChildren($this->vals, $i),
                                         (isset($this->vals[$i]['value'])) ? $this->vals[$i]['value'] : null);
     }

     //!-----------------------------------------------------------------
     // @function        SimpleXmlParser::_getChildren
     // @desc            Fun��o recursiva para a montagem da �rvore XML
     // @access          private
     // @param           vals (array)        vetor de valores do arquivo
     // @param           i    (int)          �ndice atual do vetor de valores
     // @see             PHP2Go::_getRoot
     //!-----------------------------------------------------------------
     public function _getChildren($vals, &$i)
     {
         $children = array();
         while (++$i < sizeof($vals)) {
             switch ($vals[$i]['type']) {
                    case 'cdata':       array_push($children, $vals[$i]['value']);
                                        break;
                    case 'complete':    array_push($children, new XmlNode($vals[$i]['tag'], (isset($vals[$i]['attributes']) ? $vals[$i]['attributes'] : null), null, (isset($vals[$i]['value']) ? $vals[$i]['value'] : null)));
                                        break;
                    case 'open':        array_push($children, new XmlNode($vals[$i]['tag'], (isset($vals[$i]['attributes']) ? $vals[$i]['attributes'] : null), $this->_getChildren($vals, $i), (isset($vals[$i]['value']) ? $vals[$i]['value'] : null)));
                                        break;
                    case 'close':       return $children;
               }
         }
     }

     //!-----------------------------------------------------------------
     // @function        SimpleXmlParser::_raiseError
     // @desc            Tratamento de erros da classe
     // @access          private
     // @param           errorMsg (string)   Mensagem de erro
     //!-----------------------------------------------------------------
     public function _raiseError($errorMsg)
     {
         trigger_error($errorMsg, E_USER_ERROR);
     }
}

//!-----------------------------------------------------------------
// @class      XmlNode
// @desc       Cria um nodo de �rvore XML
// @author     Marcos Pont
//!-----------------------------------------------------------------
class XmlNode
{
    public $tag;            // @var tag (string)		Tag correspondente ao nodo
    public $attrs;        // @var attrs (array)		Vetor de atributos do nodo
    public $children;        // @var children (array)	Vetor de filhos do nodo
    public $childrenCount; // @var childrenCount (int)	N�mero de filhos do nodo
    public $value;        // @var value (mixed)		Valor CDATA do nodo XML

    //!-----------------------------------------------------------------
    // @function	XmlNode::XmlNode
    // @desc		Construtor do objeto XmlNode
    // @access		public
    // @param		nodeTag (string)		Tag do nodo
    // @param		nodeAttrs (array)		Vetor de atributos do nodo
    // @param 		nodeChildren (array)	Vetor de filhos do nodo, padr�o � NULL
    // @param 		nodeValue (mixed)		Valor CDATA do nodo XML, padr�o � NULL
    //!-----------------------------------------------------------------
    public function __construct($nodeTag, $nodeAttrs, $nodeChildren = null, $nodeValue = null)
    {
        $this->tag = $nodeTag;
        $this->attrs = $nodeAttrs;
        $this->children = $nodeChildren;
        $this->childrenCount = is_array($nodeChildren) ? count($nodeChildren) : 0;
        $this->value = $nodeValue;
    }

    //!-----------------------------------------------------------------
    // @function	XmlNode::hasChildren
    // @desc		Verifica se o nodo XML possui filhos
    // @access		public
    // @returns		TRUE ou FALSE
    //!-----------------------------------------------------------------
    public function hasChildren()
    {
        return ($this->childrenCount > 0);
    }

    //!-----------------------------------------------------------------
    // @function	XmlNode::getChildrenCount
    // @desc 		Retorna o n�mero de filhos do nodo XML
    // @access 		public
    // @returns 	N�mero de filhos do nodo
    //!-----------------------------------------------------------------
    public function getChildrenCount()
    {
        return $this->childrenCount;
    }

    //!-----------------------------------------------------------------
    // @function	XmlNode::getChildren
    // @desc 		Retorna o filho de �ndice $index do nodo, se existir
    // @param 		index (int)		�ndice do nodo buscado
    // @returns 	Filho de �ndice $index ou FALSE se ele n�o existir
    //!-----------------------------------------------------------------
    public function &getChildren($index)
    {
        return (isset($this->children[$index]) ? $this->children[$index] : false);
    }

    //!-----------------------------------------------------------------
    // @function	XmlNode::getChildrenTagsArray
    // @desc 		Retorna os filhos do nodo listados em um
    // 				vetor associativo indexado pelas TAGS
    // @access 		public
    // @returns 	Vetor associativo no formato Children1Tag=>Children1Object,
    // 				Children2Tag=>Children2Object, ...
    // @note		Esta fun��o n�o deve ser utilizada quando uma TAG XML
    //				possui filhos com TAGS repetidas
    //!-----------------------------------------------------------------
    public function getChildrenTagsArray()
    {
        if (!$this->children) {
            return false;
        } else {
            $childrenArr = array();
            foreach ($this->children as $children) {
                $childrenArr[$children->tag] = $children;
            }

            return $childrenArr;
        }
    }
}
