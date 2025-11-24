# Builder

---

Builder of Document elements Query.

## Methods

### `public function query(string $tag, \Closure $closure,bool $deep = false)`

Build a query for given tag element.

#### Parameters

| Type    | Name     | Description                                                       | Default  |
|---------|----------|-------------------------------------------------------------------|----------| 
| string  | $tag     | Tag element to search for. Type `*` for any.                      | required |
| Closure | $closure | Closure for quering: *fn([Query](Query.md) $query)*               | required |
| bool    | $deep    | Flag to deep search for given tag. `//` instead of `/` for XPath. | `false`  |

#### Return value

*[Builder](Builder.md)*

### `public queryDeep(string $tag, \Closure $closure)`

Build a query for given tag element, but with deep search.

It's the same result as `query` method with `$deep = true`.

#### Parameters

| Type    | Name     | Description                                                       | Default  |
|---------|----------|-------------------------------------------------------------------|----------| 
| string  | $tag     | Tag element to search for. Type `*` for any.                      | required |
| Closure | $closure | Closure for quering: *fn([Query](Query.md) $query)*               | required |

#### Return value

*[Builder](Builder.md)*

### `public get()`

Get all query results as `Collection`

#### Return value

*Collection<int, [DocumentElement](../DocumentElement.md) | [DocumentNode](../DocumentNode.md)>*

### `public first()`

Get first query result.

#### Return value

*[DocumentElement](../DocumentElement.md)* or *[DocumentNode](../DocumentNode.md)* or *null*