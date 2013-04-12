# Data Plugin

A template Data management plugin.

Used for retrieving data from Streams and getting / setting data within the templating tags.


## Description

This data plugin will allow you to set and get variables directly in your views or templates. This helps when you want to save data for a later time, such as pulling some data from within a Streams or other tag, and saving it to reuse later.


## Usages

---

__Storing a commonly used long tag__  
```
{{ data:set key="page_autoslug" }}{{ format:url_title string=page:title sep="dash" lowercase="true" }}{{ /data:set }}
...
<a href="/page/{{ data:page_autoslug }}">Read More</a>
```

```
{{ data:set key="month" }}{{ helper:date format="F" timestamp=created_on }}{{ /data:set }}
{{ data:set key="month_lower" }}{{ helper:str_to_lower str=data:month }}{{ /data:set }}

<p>Posted in <a href="/archives/{{ data:month_lower }}">{{ data:month }}</a>.</p>
```

---

__Saving data from Streams loop for later use__  
```
{{ streams:single ... }}
  {{ data:set key="author" value=author:display_name }}
  ...
{{ /streams:single }}

... [display comments] ...

{{ if comment:author == data:author }}
  <strong>Author</strong>
{{ endif }}
```

---

__Getting Streams Data__  
```
{{ data:stream stream="articles" }}
  <h1>Articles</h1>
  <p>{{ total }} total articles</p>
  
  {{ entries }}
    ...
  {{ /entries }}
  
  {{ pagination }}
{{ /data:stream }}
```


## Methods

---

__{{ data:stream stream="dudes" ... }}__  
A duplicate of the `{{ streams:cycle }}` tag with the option to set some defaults inside your code. Defaults are set like so:

```
private $_default_stream_options = array(
	'namespace' => 'streams',
	'date_by' => 'date',
	'order_by' => 'date',
	'sort' => 'desc',
	'show_upcoming' => 'no'
);
```

Please change these to your liking. Any of the options can be overridden.

---

__{{ data:set key="key" value="some value" }}__  
Set a variable that can be retrieved later. You can choose to use this as a single tag, or as a tag pair like so:

```
{{ data:set key="tag_pair" }}{{ /data:set }}
```

Tag pairs are useful when you have a longer set of data, or data that consists of more LEX code that needs to be parsed.

---

__{{ data:get key="key" }}__  
Get a variable previously set with `{{ data:set }}`. If no variable is present, it will return `null`.

Can also be used shorthand like so:

```
// Equivalent to {{ data:get key="some_var" }}
{{ data:some_var }}
```

---

__{{ data:exists key="key" }}__  
Simply check if a var has been set.

```
{{ if {data:exists key="is_author"} }}
  I wrote this!
{{ endif }}
```


---

__{{ data:snippet id="key" }}__  
Specfically for use with the PyroSnippets module, this allows you to easily get a snippet based on a dynamic value. For example:

```
// Grab the current category from the URI String
// Example: /topics/lasers
{{ data:snippet id="category_intro_[[ segment_2 ]]" }}
```

---


## Contributing

If you have any features you think would be a nice addition to the Data Plugin, feel free to submit an issue or pull request.


## License

We have released the Data Plugin under the MIT License.