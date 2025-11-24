# Query

---

Query builder instance.

### Available query operators

- `=` for equals
- `!=` for not equals
- `contains` for contains
- `!contains` for not contains
- `has` for has
- `!has` for not has


## Methods

### `public where(string|Closure $attribute, string $operator = '=', ?string $value = null)`

Basic where clause. If provided two `where` conditions will be joined with `AND`.

#### Parameters

| Type                                           | Name       | Description                                                                                                                           | Default  |
|------------------------------------------------|------------|---------------------------------------------------------------------------------------------------------------------------------------|----------| 
| string or Closure<[Query](Query.md) $subQuery> | $attribute | Attribute name for condition or Closure for subquery.                                                                                 | required |
| string                                         | $operator  | One of [available operators](#available-operators) or if `$value` is not provided this field become `value` and used operator is `=`. | `=`      |
| string or null                                 | $value     | Value to search for.                                                                                                                  | `null`   |

#### Return value

*[Query](Query.md)*

### `public whereEquals(string $attribute, string $value)`

Where clause with equals operator.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public whereNotEquals(string $attribute, string $value)`

Where clause with doesn't equal operator.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public whereContains(string $attribute, string $value)`

Where clause with contains operator.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public whereNotContains(string $attribute, string $value)`

Where clause with doesn't contains operator.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public whereHas(string $attribute)`

Where clause with has operator. To check if attribute exists.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
#### Return value

*[Query](Query.md)*

### `public whereNotHas(string $attribute)`

Where clause with not has operator. To check if attribute doesn't exists.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
#### Return value

*[Query](Query.md)*

### `public orWhere(string|Closure $attribute, string $operator = '=', ?string $value = null)`

Basic where clause. 

If provided conditions will be joined with `OR`.

#### Parameters

| Type                                           | Name       | Description                                                                                                                           | Default  |
|------------------------------------------------|------------|---------------------------------------------------------------------------------------------------------------------------------------|----------| 
| string or Closure<[Query](Query.md) $subQuery> | $attribute | Attribute name for condition or Closure for subquery.                                                                                 | required |
| string                                         | $operator  | One of [available operators](#available-operators) or if `$value` is not provided this field become `value` and used operator is `=`. | `=`      |
| string or null                                 | $value     | Value to search for.                                                                                                                  | `null`   |

#### Return value

*[Query](Query.md)*

### `public orWhereEquals(string $attribute, string $value)`

Where clause with equals operator. 

If provided conditions will be joined with `OR`.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public orWhereNotEquals(string $attribute, string $value)`

Where clause with doesn't equal operator. 

If provided conditions will be joined with `OR`.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public orWhereContains(string $attribute, string $value)`

Where clause with contains operator.

If provided conditions will be joined with `OR`.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public orWhereNotContains(string $attribute, string $value)`

Where clause with doesn't contains operator.

If provided conditions will be joined with `OR`.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
| string | $value     | Value to search for.          | required |

#### Return value

*[Query](Query.md)*

### `public orWhereHas(string $attribute)`

Where clause with has operator. To check if attribute exists.

If provided conditions will be joined with `OR`.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
#### Return value

*[Query](Query.md)*

### `public orWhereNotHas(string $attribute)`

Where clause with not has operator. To check if attribute doesn't exists.

If provided conditions will be joined with `OR`.

#### Parameters

| Type   | Name       | Description                   | Default  |
|--------|------------|-------------------------------|----------| 
| string | $attribute | Attribute name for condition. | required |
#### Return value

*[Query](Query.md)*

### `public whereText(string $operator = '=', ?string $value = null, bool $deep = false)`

Basic where clause for text inside element.

If provided more conditions will be joined with `AND`.

#### Parameters

| Type                                           | Name       | Description                                                                                                                           | Default  |
|------------------------------------------------|------------|---------------------------------------------------------------------------------------------------------------------------------------|----------|
| string                                         | $operator  | One of [available operators](#available-operators) or if `$value` is not provided this field become `value` and used operator is `=`. | `=`      |
| string or null                                 | $value     | Value to search for.                                                                                                                  | `null`   |
| bool                                           | $deep      | If `true` will search for text inside nested elements.                                                                                | `false`  |

#### Return value

*[Query](Query.md)*

### `public whereTextEquals(string $value, bool $deep = false)`

Text where clause with equals operator.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public whereTextNotEquals(string $value, bool $deep = false)`

Text where clause with doesn't equals operator.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public whereTextContains(string $value, bool $deep = false)`

Text where clause with contains operator.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public whereTextNotContains(string $value, bool $deep = false)`

Text where clause with doesn't contains operator.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public orWhereText(string $operator = '=', ?string $value = null, bool $deep = false)`

Basic where clause for text inside element.

If provided more conditions will be joined with `OR`.

#### Parameters

| Type                                           | Name       | Description                                                                                                                           | Default  |
|------------------------------------------------|------------|---------------------------------------------------------------------------------------------------------------------------------------|----------|
| string                                         | $operator  | One of [available operators](#available-operators) or if `$value` is not provided this field become `value` and used operator is `=`. | `=`      |
| string or null                                 | $value     | Value to search for.                                                                                                                  | `null`   |
| bool                                           | $deep      | If `true` will search for text inside nested elements.                                                                                | `false`  |

#### Return value

*[Query](Query.md)*

### `public orWhereTextEquals(string $value, bool $deep = false)`

Text where clause with equals operator.

If provided more conditions will be joined with `OR`.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public orWhereTextNotEquals(string $value, bool $deep = false)`

Text where clause with doesn't equals operator.

If provided more conditions will be joined with `OR`.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public orWhereTextContains(string $value, bool $deep = false)`

Text where clause with contains operator.

If provided more conditions will be joined with `OR`.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public orWhereTextNotContains(string $value, bool $deep = false)`

Text where clause with doesn't contains operator.

If provided more conditions will be joined with `OR`.

#### Parameters

| Type           | Name   | Description                                            | Default |
|----------------|--------|--------------------------------------------------------|---------|
| string or null | $value | Value to search for.                                   | `null`  |
| bool           | $deep  | If `true` will search for text inside nested elements. | `false` |

#### Return value

*[Query](Query.md)*

### `public toQueryString()`

Returns query XPath string.

#### Return value

*string*