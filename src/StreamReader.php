<?php

namespace PHPXML\TextNode;

final class StreamReader
{

    /**
     * @var int bytes to read
     */
    private $bytes;

    /**
     * @var resource
     */
    private $input;

    /**
     * @var string
     */
    private $target;

    /**
     * @var resource
     */
    private $output;

    /**
     * ResourceReader constructor.
     * @param resource $input
     * @param string $target
     * @param $output
     * @param int $bytes
     */
    public function __construct($input, string $target, $output, $bytes = 4096)
    {
        $this->input = $input;
        $this->target = $target;
        $this->output = $output;
        $this->bytes = $bytes;
        $this->parser = new Parser($target, $output);
    }

    /**
     * Parse the input by iterating over small parts
     */
    public function parse()
    {
        // Continue reading until a match is found
        while ($data = $this->read()) {
            if ($this->found($data)) {
                return;
            }
        }

        throw new NoMatchFound("Could not find {$this->target}");
    }

    /**
     * @return string Read a couple bytes
     */
    private function read() : string
    {
        return fread($this->input, $this->bytes);
    }

    /**
     * @param string $data
     * @return bool
     */
    private function found(string $data) : bool
    {
        return $this->parser->parse($data, feof($this->input));
    }
}