# Document

---

Base class to represent and manipulate HTML or XML document.

##### Extends [DocumentNode](DocumentNode.md)

## Methods

### `public static loadHtml(string $html, string $version = '1.0', string $encoding = '')`

Create new instance from HTML string.

#### Parameters

| Type   | Name      | Description         | Default  |
|--------|-----------|---------------------|----------| 
| string | $html     | HTML string content | required |
| string | $version  | Document version    | `'1.0'`  |
| string | $encoding | Document encoding   | `''`     |

#### Return value
*[Document](Document.md)*

### `public static loadXml(string $xml, string $version = '1.0', string $encoding = '')`

Create new instance from XML string.

#### Parameters

| Type   | Name      | Description        | Default  |
|--------|-----------|--------------------|----------| 
| string | $xml      | XML string content | required |
| string | $version  | Document version   | `'1.0'`  |
| string | $encoding | Document encoding  | `''`     |

#### Return value
*[Document](Document.md)*

### `public toNative()`

Return native DOMDocument instance.

#### Return value
*DOMDocument*