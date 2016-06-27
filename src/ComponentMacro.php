<?php

namespace WebChemistry\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class ComponentMacro extends MacroSet {

	/** @var string */
	public $directory;

	/** @var array */
	protected $imports = [];

	/**
	 * @param Compiler $compiler
	 * @param string $directory
	 */
	public static function install(Compiler $compiler, $directory) {
		$me = new self($compiler);
		$me->directory = $directory;

		$me->addMacro('component', NULL, [$me, 'macroComponent']);
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function macroComponent(MacroNode $node, PhpWriter $writer) {
		$isEmpty = $node->htmlNode->isEmpty;
		$tagName = $node->htmlNode->name;
		$prepend = $isEmpty ? '' : '<?php ob_start(); ?> ';

		// modifiers
		if (isset($node->htmlNode->attrs['modifiers'])) {
			$node->modifiers = $node->htmlNode->attrs['modifiers'];
			unset($node->htmlNode->attrs['modifiers']);
		}

		list($file, $blockName) = $this->parsePath($node->htmlNode->name);

		$inside = $isEmpty ? 'NULL' : 'ob_get_clean()';
		$code = "// WebChemistry\\Macros\\ComponentMacro\n";
		if ($blockName) {
			if (!isset($this->imports[$file])) {
				$this->imports[$file] = TRUE;
				$code .= "\$_foo = $inside;\n";
				$code .= $writer->write(
						'$this->createTemplate(%var, $this->params, "import")->render();',
						$this->directory . $file
					) . "\n";
			}
			$code .= $writer->write(
				'$this->renderBlock(%word, $this->params + %var + %node.array? + ["_content" => $_foo]%raw);',
				$blockName,
				$node->htmlNode->attrs,
				$this->modify($node, $writer)
			);
		} else {
			$code .= $writer->write(
				'$_content = ' . $inside . ';' .
				'$tmpl = $this->createTemplate(%var, $this->params + %var + %node.array? + ["_content" => $_content], "include");' .
				'$tmpl->blockQueue = array_merge_recursive($this->blockQueue, $tmpl->blockQueue);' .
				'$tmpl->blockTypes = $this->blockTypes;' .
				'$tmpl->renderToContentType(%raw);',
				$this->directory . $file,
				$node->htmlNode->attrs,
				$this->modify($node, $writer, '"html"')
			);
		}

		$node->content = preg_replace("~<$tagName.*?>(.*)<\\/$tagName>~s", $prepend . $node->innerContent . '<?php ' . $code . ' ?>', $node->content);
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @param string $else
	 * @param bool $comma
	 * @return string
	 */
	private function modify(MacroNode $node, PhpWriter $writer, $else = '', $comma = TRUE) {
		if (!$node->modifiers) {
			return $else;
		}

		return $writer->write(
			($comma ? ', ' : '') . 'function ($s, $type) { $_fi = new LR\FilterInfo($type); return %modifyContent($s); }'
		);
	}

	/**
	 * @param $name
	 * @return array [directory, blockName]
	 */
	private function parsePath($name) {
		preg_match_all('~[A-Z]?[^A-Z]+~', $name, $matches);
		$matches = array_map(function ($val) {
			return lcfirst($val);
		}, $matches[0]);
		$len = count($matches);

		return [
			'/'. ($len === 1 ? $matches[0] : implode('/', array_slice($matches, 0, $len - 1))) . '.latte',
			$len === 1 ? NULL : $matches[$len - 1]
		];
	}

}
