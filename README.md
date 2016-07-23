# Large XML text nodes in PHP

The more high end XML APIs of PHP (e.g. SimpleXML) have the disadvantage that they store the whole XML document in memory. Parsing large XML files is therefore not the easiest task.

This package is designed to read large text nodes in a memory friendly fashion, so that for instance base64 encoded files saved in XML text nodes can be written to a file.

## Selecting text nodes

To read the contents of a text node, a **path** must be provided. To keep things simple, a path is just a list of tag names separated by a forward slash `/`. Currently only the first match in the XML document is returned. 

For instance, the path `"document/tag/name"` would match the `I am matched` text node.
```xml
<document>
    <tag>
        <name>I am matched</name>
    </tag>
    <tag>
        <name>I am skipped</name>
    </tag>
</document>
```
 
## Examples

To give you an idea, see the following examples

### Reading a large XML file
```php
<?php
// php://temp writes first to memory, but if the text node is large, a temporary file is used on the fly.
$output = fopen('php://temp', 'w+');
$input = fopen('some-large-file.xml', 'r');
$path = 'some/target';

// Use the resource reader to parse the XML document
try {
    $reader = new StreamReader($input, $path, $output);
    $reader->parse();
    rewind($output);
    // Maybe do something with $output
} catch (XMLException $e) {
    // Whoops
} finally {
    fclose($output);
    fclose($input);
}
```

### Converting a base64 encoded text node to a file
```php
// Assume $input is some given stream
$input = fopen('some-file');
$path = 'some/target';

// Open an output stream and add PHP's own base64 decoder filter
$output = fopen('php://temp', 'w+');
stream_filter_append($output, 'convert.base64-decode');

try {
    $reader = new StreamReader($input, $path, $output);
    $reader->parse();
    rewind($output);
} catch (XMLException $e) {
    // Whoops
} finally {
    fclose($output);
    fclose($input);
}
```