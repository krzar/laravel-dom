# DocumentElement

---

Document element instance.

##### Extends [DocumentNode](DocumentNode.md)

## Methods

### `public static create(string $tag)`

Creates empty instance of DocumentElement with given `$tag`.

#### Parameters

| Type   | Name | Description      | Default  |
|--------|------|------------------|----------| 
| string | $tag | Element tag name | required |

#### Return value
*[DocumentElement](DocumentElement.md)*

### `public id()`

Return DOM Element `id` attribute.

#### Return value

*string* or *null*

### `public setId(string $id)`

Set element `id` attribute value.

#### Parameters

| Type   | Name | Description       | Default  |
|--------|------|-------------------|----------| 
| string | $id  | Element id to set | required |

#### Return value
*void*

### `public className()`

Return DOM Element `class` attribute.

#### Return value

*string* or *null*

### `public classes()`

Return `Collection` of DOM Element `class` attribute values.

#### Return value

*Collection<int, string>*

### `public hasClass(string $className)`

Check if Element has given class name.

#### Parameters

| Type   | Name       | Description         | Default  |
|--------|------------|---------------------|----------| 
| string | $className | Class name to check | required |

#### Return value
*bool*

### `public addClass(string $className)`

Add new class name to element.

#### Parameters

| Type   | Name       | Description       | Default  |
|--------|------------|-------------------|----------| 
| string | $className | Class name to add | required |

#### Return value
*void*

### `public removeClass(string $className)`

Remove given class name from element.

#### Parameters

| Type   | Name       | Description          | Default  |
|--------|------------|----------------------|----------| 
| string | $className | Class name to remove | required |

#### Return value
*void*

### `public attributes()`

Return `Collection` of DOM Element attributes. Where key is attribute name and value is attribute value.

#### Return value

*Collection<string, string>*

### `public hasAttribute(string $name)`

Check if Element has given attribute.

#### Parameters

| Type   | Name  | Description             | Default  |
|--------|-------|-------------------------|----------| 
| string | $name | Attribute name to check | required |

#### Return value
*bool*

### `public setAttribute(string $name, mixed $value)`

Set given attribute value or create given attribute if not exist.

#### Parameters

| Type   | Name   | Description                                       | Default  |
|--------|--------|---------------------------------------------------|----------| 
| string | $name  | Attribute name to set                             | required |
| mixed  | $value | Attribute value to set, always castes do `string` | required |

#### Return value
*void*

### `public removeAttribute(string $name)`

Remove given attribute from element.

#### Parameters

| Type   | Name  | Description              | Default  |
|--------|-------|--------------------------|----------| 
| string | $name | Attribute name to remove | required |

#### Return value
*void*

### `public tagName()`

Return name of DOM Element tag. For example: `div`.

#### Return value

*string*

### `public parent()`

Return parent of current element. If element has no parent, return `null`.

#### Return value

*[DocumentElement](DocumentElement.md)* or *[DocumentNode](DocumentNode.md)* or `null`

### `public append(DocumentElement $documentElement)`

Append given [DocumentElement](DocumentElement.md) on the end of DocumentElement.

#### Parameters

| Type                                  | Name             | Description               | Default  |
|---------------------------------------|------------------|---------------------------|----------|
| [DocumentElement](DocumentElement.md) | $documentElement | DocumentElement to append | required |

#### Return value
*void*

### `public prepend(DocumentElement $documentElement)`

Prepend given [DocumentElement](DocumentElement.md) on the beginning of DocumentElement.

#### Parameters

| Type                                  | Name             | Description                | Default  |
|---------------------------------------|------------------|----------------------------|----------|
| [DocumentElement](DocumentElement.md) | $documentElement | DocumentElement to prepend | required |

#### Return value
*void*

### `public remove()`

Remove current DocumentElement from the Document.

#### Return value
*void*

### `public toNative()`

Return Native DOMElement instance.

#### Return value

*DOMElement*