<?php
//LL
if(isset($_GET["source"])) {

    $functions = array("default", "html", "keyword", "string", "comment");
    foreach ($functions as $value) {
        ini_set("highlight.$value", "highlight-$value;");
    }
    $content = highlight_file($_SERVER["SCRIPT_FILENAME"], TRUE);
    foreach($functions as $value) {
        $content = preg_replace("/style=\"color: highlight-$value;\"/", "class=\"highlight-$value\"", $content);
    }
    $content = preg_replace(
        "/^<code><span class=\"highlight-html\">(.+)<\/span>\r?\n?<\/code>\$/s",
        "<code>$1</code>",
        $content
    );
    $content = preg_replace(
        "/\&lt;(\w+)\&nbsp;(.+?)(\/?)\&gt;/",
        "<span class=\"highlight-html\">&lt;<span class=\"highlight-tag\">$1</span>&nbsp;<span class=\"highlight-default\">$2</span>$3&gt;</span>",
        $content
    );
    $content = preg_replace(
        "/\&lt;(\/?)(\w+)\&gt;/",
        "<span class=\"highlight-html\">&lt;$1<span class=\"highlight-tag\">$2</span>&gt;</span>",
        $content
    );
    $content = preg_replace(
        "/highlight-default\">(\&lt;\?php|\?\&gt;)/",
        "highlight-tag\">$1",
        $content
    );
    $content = preg_replace(
        "/<span class=\"highlight-default\">(.*?)([a-zA-Z_]+)<\/span>\r?\n?<span class=\"highlight-keyword\">\(/",
        "<span class=\"highlight-default\">$1</span><span class=\"highlight-function\">$2</span><span class=\"highlight-punctuation\">(",
        $content
    );
    $content = preg_replace(
        "/<span class=\"highlight-keyword\">(.*?)((&.{2,4};|[,();=.])+)<\/span>/",
        "<span class=\"highlight-keyword\">$1<span class=\"highlight-punctuation\">$2</span></span>",
        $content
    );
    $content = preg_replace(
        "/<span class=\"highlight-keyword\">(\w*)((&.{2,4};|[,();=.])+)/",
        "<span class=\"highlight-keyword\">$1<span class=\"highlight-punctuation\">$2</span>",
        $content
    );
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noimageindex, nofollow, nosnippet">
    <title>${_SERVER["SCRIPT_FILENAME"]}</title>
    <style>
@media (prefers-color-scheme: dark) {
    body { color: #d4d4d4; background-color: #1e1e1e; }
    .highlight-html { color: #808073; }
    .highlight-tag { color: #569cd6; }
    .highlight-default { color: #9cdcfe; }
    .highlight-function { color: #dcdcaa; }
    .highlight-keyword { color: #c586c0; font-weight: bold; }
    .highlight-punctuation { color: #c9d4d4; font-weight: bold; }
    .highlight-string { color: #ce9178; }
    .highlight-comment { color: #6a9955; }
}
@media (prefers-color-scheme: light) {
    .highlight-html { color: #808073; }
    .highlight-tag { color: #881280; }
    .highlight-default { color: #0000bb; }
    .highlight-function { color: #0000bb; }
    .highlight-keyword { color: #007700; font-weight: bold; }
    .highlight-punctuation { color: #1e1e1e; font-weight: bold; }
    .highlight-string { color: #dd0000; }
    .highlight-comment { color: #ff8000; }
}
    </style>
</head>
<body>
$content
</body>
</html>
HTML;
    exit();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>