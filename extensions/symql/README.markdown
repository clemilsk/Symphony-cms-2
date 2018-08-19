# SymQL

## Rationale

Querying entries within your own PHP applications can be a hit-and-miss affair. Building the JOINs of field tables is time consuming, but a necessary evil even if you want to use the `EntryManager` class (which requires you to pass JOINs and WHERE strings of SQL to it). SymQL continues what [DatabaseManipulator](http://github.com/yourheropaul/databasemanipulator/) started, and is intended as a full replacement.

Essentially SymQL is a wrapper for the `EntryManager` class. It shares many similarities with Symphony data sources, and it shares the "filter" syntax to compile its WHERE queries. It does however add functionality beyond normal data sources in that it allows you to perform OR queries between fields, for example:

	WHERE (name='Alistair' OR name='Allen') AND published='yes'

The primary aim of this extension is to provide human-readable, object-oriented access to Symphony entries, in order to make custom data sources a whole lot easier.

## Performance

SymQL is just a wrapper around an ugly class. It has no performance gains over standard Symphony data sources, but similarly it does not incur extra queries either. If you want the best performance then you should be writing SQL statements to perform the JOINs yourself. Append `?profile=database-queries` to your page to view Symphony's database queries.

## Usage

SymQL is designed to be _readable_ — methods are chainable, so the syntax looks like SQL. Start by including the SymQL class:

	require_once(EXTENSIONS . '/symql/lib/class.symql.php');

Build a new query:

	$query = new SymQLQuery();

The following methods can be called on the query object:

### `select`
Specify the fields to return for each entry. Accepts a comma-delimited string of field handles or IDs. For example:

	$query->select('*'); // all fields

	$query->select('name, body, published'); // field handles

	$query->select('name, 4, 12, comments:items'); // mixture of handles, IDs and fields with "modes" e.g. Bi-Link Field

	$query->select('system:count'); // count only, no entries built

### `from`
Specify the section from which entries should be queried. Accepts either a section handle or ID. For example:

	$query->from('articles');

### `where`
Build a series of select criteria. Use Symphony data source filtering syntax, although parameters using the curly-brace syntax (e.g. `{$root}`) are **not** supported. Accepts a field (handle or ID), the filter value, and an optional type. For example:

	$query->where('published', 'yes'); // filters a checkbox "Published" with a Yes value

	$query->where('title', 'regexp:CSS'); // filters a text input "Title" with a regular expression

As with a data source, filters combine at the SQL level with the `AND` keyword. Therefore using both of the filters above would select entries where Published is `Yes` _and_ Title is like `CSS`. If you want the filters to combine using the `OR` keyword, pass your preference as the optional third argument:

	$query->where('title', 'regexp:Allen');
	$query->where('title', 'regexp:Alistair', SymQL::DS_FILTER_OR);
	$query->where('published', 'yes', SymQL::DS_FILTER_AND);

The above will find published entries where the Title matches either `Allen` or `Alistair`, and where Published is `Yes`. Note that the default is `SymQL::DS_FILTER_AND`.

System parameters can also be filtered on, as with a normal data source:

	$query->where('system:id', 15);

	$query->where('system:date', 'today');

### `orderby`

Specify sort and direction. Defaults to `system:id` in `desc` order. Use a valid field ID, handle or system pseudo-field (`system:id` or `system:date`). Direction values are `asc`, `desc` or `rand`. Case insensitive.

	$query->orderby('system:date', 'asc');

	$query->orderby('name', 'DESC');

### `perPage`

Number of entries to limit by. If you don't want to use pagination, use this as an SQL `LIMIT`.

	$query->perPage(20);

### `page`

Which page of entries to return.

	$query->page(2);

## Run the query!

To run the query, pass the `SymQLQuery` object to the `SymQL::run()` method:

	$result = SymQL::run($query); // returns an XMLElement of matching entries

SymQL can return entries in four different flavours depending on how you want them. Provide the desired output mode as the second argument to the `run` method. For example:

	$result = SymQL::run($query, SymQL::RETURN_ENTRY_OBJECTS); // returns an array of Entry objects

* `RETURN_XML` (default) returns an XMLElement object, almost identical to a data source output
* `RETURN_ARRAY` returns a PHP array (same structure as RETURN_XML, but entry XMLElements are converted into an array)
* `RETURN_RAW_COLUMNS` returns the raw column values from the database for each field
* `RETURN_ENTRY_OBJECTS` returns an array of Entry objects, useful for further processing

When using the default `RETURN_XML` type, the root element is named `symql` by default. To change this, pass the element name when constructing the query:

	$query = new SymQLQuery('element-name-here');

## A simple example

Try this inside a customised data source. The data source should return $result, which by default is returned from SymQL as an XMLElement.

	// include SymQL
	require_once(EXTENSIONS . '/symql/lib/class.symql.php');
	
	// create a new SymQLQuery
	$query = new SymQLQuery('published-articles');
	$query
		->select('title, content, date, publish')
		->from('articles')
		->where('published', 'yes')
		->orderby('system:date', 'desc')
		->perPage(10)
		->page(1);
		
	$result = SymQL::run($query);

## Debugging
Basic debug information can be returned by calling `SymQL::getDebug()` after running your query. It returns an array of query counts and SQL fragments.

	var_dump(SymQL::getDebug());die;

## Known issues
* none as of 0.6. But that doesn't mean there aren't bugs left to discover...