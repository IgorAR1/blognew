<?php

use Latte\Runtime as LR;

/** source: hello.latte */
final class Template_1a759561b6 extends Latte\Runtime\Template
{
	public const Source = 'hello.latte';


	public function main(array $ʟ_args): void
	{
		extract($ʟ_args);
		unset($ʟ_args);

		echo '<!DOCTYPE html>
<html>
<head>
    <title>Hello Page</title>
</head>
<body>
<h1>Hello, ';
		echo LR\Filters::escapeHtmlText($name) /* line 7 */;
		echo '!</h1>
<form method="post">
    <input type="text" name="name" value="';
		echo LR\Filters::escapeHtmlAttr($name) /* line 9 */;
		echo '" placeholder="Your name">
    <button type="submit">Send Hello</button>
</form>
';
		if ($name !== 'Guest') /* line 12 */ {
			echo '    <p>Welcome back, ';
			echo LR\Filters::escapeHtmlText($name) /* line 13 */;
			echo '!</p>
';
		} else /* line 14 */ {
			echo '    <p>Please enter your name!</p>
';
		}
		echo '</body>
</html>';
	}
}
