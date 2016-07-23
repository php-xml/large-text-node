<?php

namespace PHPXML\TextNode;

final class Parser
{
    /**
     * Keeps track of the depth in the XML tree
     * @var int
     */
    private $depth;

    /**
     * Keeps track of the depth of the last match
     * @var int
     */
    private $matchLevel;

    /**
     * Once a full match is found, this is set to true
     * @var bool
     */
    private $done;

    /**
     * Stack for keeping unvisited elements to be matched
     * @var array
     */
    private $unvisited = [];

    /**
     * Stack for keeping matched elements
     * @var array
     */
    private $visited = [];

    /**
     * Some XML resource returned by xml_parser_create
     * @var resource
     */
    private $xml;

    /**
     * @var resource Output resource to write text node contents to
     */
    private $output;

    /**
     * Parser constructor.
     * @param string $target
     * @param resource $output
     */
    public function __construct($target, $output)
    {
        $this->unvisited = explode('/', strtoupper($target));
        $this->output = $output;
        $this->depth = 0;
        $this->matchLevel = 0;
        $this->done = false;

        $this->xml = xml_parser_create();
        xml_set_object($this->xml, $this);
        xml_set_element_handler($this->xml, 'start', 'end');
    }

    /**
     * Returns true if currently in a full match
     * @return bool
     */
    private function match() : bool
    {
        return empty($this->unvisited);
    }

    /**
     * Handler for values
     * @param $parser
     * @param $data
     */
    protected function character($parser, $data)
    {
        fwrite($this->output, trim($data));
    }

    /**
     * Handler for the start of a tag
     * @param $parser
     * @param $tag
     * @throws NotATextNode
     */
    protected function start($parser, $tag)
    {
        if ($this->match()) {
            throw new NotATextNode();
        }

        // Check for a new match and remove it from the stack
        if ($this->matchLevel === $this->depth && $tag === reset($this->unvisited)) {
            $this->matchLevel++;
            array_unshift($this->visited, array_shift($this->unvisited));

            if ($this->match()) {
                xml_set_character_data_handler($this->xml, 'character');
            }
        }

        $this->depth++;
    }

    /**
     * Handler for the end of a tag
     * @param $parser
     */
    protected function end($parser)
    {
        // Add previous match back on the unvisited stack
        if ($this->depth === $this->matchLevel) {

            // End of a match: better stop handling the rest of the XML tree
            if ($this->match()) {
                xml_set_element_handler($parser, null, null);
                xml_set_character_data_handler($parser, null);
                $this->done = true;
            }

            $this->matchLevel--;
            array_unshift($this->unvisited, array_shift($this->visited));
        }

        $this->depth--;
    }

    /**
     * @param string $part
     * @param bool $eof
     * @return bool returns true if done
     * @throws XMLException
     */
    public function parse(string $part, bool $eof) : bool
    {
        if (xml_parse($this->xml, $part, $eof) === 0) {
            throw new XMLException("Parser error: " . xml_error_string(xml_get_error_code($this->xml)));
        }

        return $this->done;
    }

    /**
     * Free the parser
     */
    public function __destruct()
    {
        xml_parser_free($this->xml);
    }
}


class XMLException extends \RuntimeException
{
}

class NoMatchFound extends XMLException
{
}

class NotATextNode extends XMLException
{
}