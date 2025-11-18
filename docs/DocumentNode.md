# DocumentNode

---

Base node class for every document node.

## Methods

### `public query(string $tag, \Closure $closure, bool $deep = false)`

Begin building query for nodes.

#### Parameters

| Type                             | Name     | Description                                           | Default  |
|----------------------------------|----------|-------------------------------------------------------|----------| 
| string                           | $tag     | Tag name to query for, `*` to query any.              | required |
| Closure<[Query](Query/Query.md)> | $closure | Query Closure with conditions.                        | required |
| bool                             | $deep    | Option to make deep search. XPath `//` instead of `/` | `false`  |

#### Return value

*[Builder](Query/Builder.md)*

### `public queryDeep(string $tag, \Closure $closure)`

Begin building query for nodes with deep search. (Alternative to use `$deep` in `query` method)

#### Parameters

| Type                             | Name     | Description                                           | Default  |
|----------------------------------|----------|-------------------------------------------------------|----------| 
| string                           | $tag     | Tag name to query for, `*` to query any.              | required |
| Closure<[Query](Query/Query.md)> | $closure | Query Closure with conditions.                        | required |

#### Return value

*[Builder](Query/Builder.md)*

### `public text()`

Return text content of node.

#### Return value

*string*

### `public attribute(string $name, mixed $default = null)`

Return node attribute value.

#### Parameters

| Type   | Name     | Description                                   | Default  |
|--------|----------|-----------------------------------------------|----------| 
| string | $name    | Name of attribute                             | required |
| string | $default | Default returned value if attribute not exist | `null`   |


#### Return value
*mixed*

### `public html()`

Return HTML content of node if exists, otherwise `null`.

#### Return value

*string* or *null*

### `public toNative()`

Return native DOMNode object.

#### Return value

*DOMNode*

### `public document()`

Return root Document instance.

#### Return value

*[Document](Document.md)*

### `public children()`

Return collection of children nodes.

#### Return value

*Collection<[DocumentNode](DocumentNode.md) | [DocumentElement](DocumentElement.md)>*

### `public parent()`

Return parent node.

#### Return value

*[DocumentNode](DocumentNode.md)* or *null*

### `public previousSibling()`

Return previous sibling node.

#### Return value

*[DocumentNode](DocumentNode.md)* or *null*

### `public nextSibling()`

Return next sibling node.

#### Return value

*[DocumentNode](DocumentNode.md)* or *null*