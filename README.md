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
	<TemplateFirst n:component /> <!-- Render block 'first' -->
	<TemplateSecond n:component /> <!-- Render block 'second' -->
```

Same in nette:
```html
{import __DIR__ . '/component-dir/template.latte'}

{include first}
{include second}
```

template.latte
```html
{define first}

{/define}

{define second}

{/define}
```

## Custom parameters

```html
	<Template n:component="foo => bar" atrrParameter="val" />
````

Same in nette:
```html
	{include __DIR__ . '/component-dir/template.latte' foo => bar, attrParameter => val}
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
