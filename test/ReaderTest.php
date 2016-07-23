<?php

namespace PHPXML\TextNode\Test;

use PHPUnit\Framework\TestCase;
use PHPXML\TextNode\ResourceReader;

class ReaderTest extends TestCase
{

    /** @test */
    public function the_resource_reader_can_be_used()
    {
        $output = fopen('php://memory', 'w+');
        $input = fopen('php://memory', 'w+');
        fwrite($input, $this->validXML());
        rewind($input);

        $reader = new ResourceReader($input, 'some/target', $output, 16);
        $reader->parse();

        rewind($output);
        $this->assertEquals('Target content', stream_get_contents($output));
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
}