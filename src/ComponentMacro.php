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
		$prepend = $isEmpty ? '' : '<?php ob_start(); ?> ';
		$node->content = $prepend . $node->innerContent;

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
						'$this->createTemplate(%word, $this->params, "import")->render();',
						addslashes($this->directory . $file)
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
				'$this->createTemplate(%word, $this->params + %var + %node.array? + ["_content" => ' . $inside . '], "include")' .
				'->renderToContentType(%raw);',
				addslashes($this->directory . $file),
				$node->htmlNode->attrs,
				$this->modify($node, $writer, '"html"')
			);
		}

		return $code;
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @param string $else
	 * @return string
	 */
	private function modify(MacroNode $node, PhpWriter $writer, $else = '') {
		if (!$node->modifiers) {
			return $else;
		}

		return $writer->write(', function ($s, $type) { $_fi = new LR\FilterInfo($type); return %modifyContent($s); }');
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
