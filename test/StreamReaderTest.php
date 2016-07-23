<?php

namespace PHPXML\TextNode\Test;

use PHPUnit\Framework\TestCase;
use PHPXML\TextNode\NoMatchFound;
use PHPXML\TextNode\StreamReader;

class ResourceReaderTest extends TestCase
{

    /** @test */
    public function the_resource_reader_can_be_used()
    {
        $output = fopen('php://memory', 'w+');
        $input = fopen('php://memory', 'w+');
        fwrite($input, $this->validXML());
        rewind($input);

        $reader = new StreamReader($input, 'some/target', $output, 16);
        $reader->parse();

        rewind($output);
        $this->assertEquals('Target content', stream_get_contents($output));
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

        $reader = new StreamReader($input, 'some/target', $output, 16);
        $reader->parse();
        fclose($output);
        fclose($input);
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
}