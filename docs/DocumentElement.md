# DocumentElement

---

Document element instance.

##### Extends [DocumentNode](DocumentNode.md)

## Methods

### `public id()`

Return DOM Element `id` attribute.

#### Return value

*string* or *null*

### `public className()`

Return DOM Element `class` attribute.

#### Return value

*string* or *null*

### `public classes()`

Return `Collection` of DOM Element `class` attribute values.

#### Return value

*Collection<int, string>*

### `public tagName()`

Return name of DOM Element tag. For example: `div`.

#### Return value

*string*

### `public parent()`

Return parent of current element. If element has no parent, return `null`.

#### Return value

*[DocumentElement](DocumentElement.md)* or *[DocumentNode](DocumentNode.md)* or `null`

### `public attributes()`

Return `Collection` of DOM Element attributes. Where key is attribute name and value is attribute value.

#### Return value

*Collection<string, string>*

### `public toNative()`

Return Native DOMElement instance.

#### Return value

*DOMElement*