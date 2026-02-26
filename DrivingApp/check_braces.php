<?php
$content = file_get_contents('routes/web.php');
$tokens = token_get_all($content);
$open = 0;
foreach ($tokens as $idx => $token) {
    if (is_string($token)) {
        if ($token === '{')
            $open++;
        if ($token === '}')
            $open--;
    }
    elseif ($token[0] === T_CURLY_OPEN || $token[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
        $open++;
    }
    if ($open < 0) {
        $line = is_array($token) ? $token[2] : "unknown";
        echo "Too many closing braces at line $line\n";
        break;
    }
}
if ($open > 0)
    echo "Missing $open closing braces\n";
if ($open == 0)
    echo "Braces are balanced\n";
