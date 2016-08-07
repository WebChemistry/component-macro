# Component-Macro
Replacement of include, import from nette with more beautiful and shorter version.

[![Build Status](https://travis-ci.org/WebChemistry/component-macro.svg?branch=master)](https://travis-ci.org/WebChemistry/component-macro)

## Installation

```php
	WebChemistry\Macros\ComponentMacro::install($latte->getCompiler(), __DIR__ . '/component-dir');
```

## Usage single file

```html
	<Template n:component />
```
~
Same in nette:
```html
	{include __DIR__ . '/component-dir/template.latte'}
````
Renders content from component-dir/template.latte

## Usage single file, multiple blocks

```html
	<TemplateFirst block n:component /> <!-- Renders block 'first' -->
	<TemplateSecond block n:component="key => value" /> <!-- Renders block 'second' -->
```

Same in nette:
```html
{import __DIR__ . '/component-dir/template.latte'}

{include #first}
{include #second key => value}
```

template.latte
```html
{define first}
    ...
{/define}

{define second}
    {$key}
    ...
{/define}
```

## Usage file from other directory

```html
    <TemplateFirst n:component />
```

Same in nette:
```html
{include __DIR__ . '/component-dir/template/first.latte'}
```

template/first.latte:
```html
...
```

## Custom parameters

```html
	<Template n:component="foo => bar" />
````

Same in nette:
```html
	{include __DIR__ . '/component-dir/template.latte' foo => bar}
```

template.latte
```html
	{$foo}
```

## Content

```html
	<Template n:component>
		Content with dynamic parameter or with macros <Template n:component />
	</Template>
```

Same in nette:
```html
{capture $foo}
	Content with dynamic parameter or with macros {include __DIR__ . '/component-dir/template.latte'}
{/capture}
{include __DIR__ . '/component-dir/template.latte' _content => $foo}
```

template.latte
```html
{!$_content}
```

## Modifiers

```html
	<Template modifiers="stripHtml|truncate:500" n:component>
    		Content with dynamic parameter or with macros <Template n:component />
    </Template>
```

Same in nette:
```html
{include __DIR__ . '/component-dir/template.latte' _content => $foo|stripHtml|truncate:500}
```
