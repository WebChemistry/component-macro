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
	 * @param MacroNode $macroNode
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function macroComponent(MacroNode $macroNode, PhpWriter $writer) {
		$isEmpty = $macroNode->htmlNode->isEmpty;
		$prepend = $isEmpty ? '' : '<?php ob_start(); ?> ';
		$macroNode->content = $prepend . $macroNode->innerContent;
		list($file, $blockName) = $this->parsePath($macroNode->htmlNode->name);

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
				'$this->renderBlock(%word, $this->params + %var + %node.array? + ["_content" => $_foo]);',
				$blockName,
				$macroNode->htmlNode->attrs
			);
		} else {
			$code .= $writer->write(
				'$this->createTemplate(%word, $this->params + %var + %node.array? + ["_content" => ' . $inside . '], "include")' .
				'->renderToContentType("html");',
				addslashes($this->directory . $file),
				$macroNode->htmlNode->attrs
			);
		}

		return $code;
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
