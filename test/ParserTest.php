<?php

namespace PHPXML\TextNode\Test;

use PHPUnit\Framework\TestCase;
use PHPXML\TextNode\NoMatchFound;
use PHPXML\TextNode\NotATextNode;
use PHPXML\TextNode\Parser;
use PHPXML\TextNode\ResourceReader;
use PHPXML\TextNode\XMLException;


class ParserTest extends TestCase
{
    /** @test */
    public function it_reads_the_contents_of_a_target()
    {
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('some/target', $output);
        $result = $parser->parse($this->validXML(), true);
        $this->assertTrue($result);

        rewind($output);
        $this->assertEquals('Target content', stream_get_contents($output));
        fclose($output);
    }

    /** @test */
    public function target_path_is_case_insensitive()
    {
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('SoMe/TaRgEt', $output);
        $result = $parser->parse($this->validXML(), true);
        $this->assertTrue($result);

        rewind($output);
        $this->assertEquals('Target content', stream_get_contents($output));
        fclose($output);
    }

    /** @test */
    public function partial_matches_are_partial()
    {
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('first', $output);
        $this->assertFalse($parser->parse('<first>Hello ', false));
        $this->assertTrue($parser->parse('world</first>', true));

        rewind($output);
        $this->assertEquals('Hello world', stream_get_contents($output));
        fclose($output);
    }

    /** @test */
    public function it_ignores_whitespace()
    {
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('first', $output);
        $parser->parse('<first>  Hello world  </first>', true);

        rewind($output);
        $this->assertEquals('Hello world', stream_get_contents($output));
        fclose($output);
    }

    /** @test */
    public function when_no_match_is_found_it_should_not_write_to_output()
    {
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('some/target', $output);
        $parser->parse($this->noMatch(), true);
        rewind($output);
        $this->assertEmpty(stream_get_contents($output));
        fclose($output);
    }

    /** @test */
    public function when_no_match_is_found_in_the_reader_it_should_throw()
    {
        $this->expectException(NoMatchFound::class);

        $input = fopen('php://memory', 'w+');
        fwrite($input, $this->noMatch());
        rewind($input);

        $output = fopen('php://memory', 'w+');

        $reader = new ResourceReader($input, 'some/target', $output, 16);
        $reader->parse();
        fclose($output);
        fclose($input);
    }

    /** @test */
    public function when_the_xml_is_invalid_an_error_is_thrown()
    {
        $this->expectException(XMLException::class);
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('some/target', $output);
        $parser->parse($this->invalidXMLPartialMatch(), true);
        fclose($output);
    }

    /** @test */
    public function when_match_is_not_a_text_node_an_error_is_thrown()
    {
        $this->expectException(NotATextNode::class);
        $output = fopen('php://memory', 'w+');
        $parser = new Parser('some/parent', $output);
        $parser->parse($this->validXML(), true);
        fclose($output);
    }

    private function validXML()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<some>
    <nested><tag>Here</tag></nested>
    <parent>
        <with>one</with>
        <children>two</children>
    </parent>
    <target>Target content</target>
    <ommitted>Tag</ommitted>
</some>
XML;
    }

    private function noMatch()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<some>
    <nothing>Tag</nothing>
</some>
XML;
    }

    private function invalidXMLPartialMatch()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<some>
    <target>Tag
</some>
XML;
    }
}