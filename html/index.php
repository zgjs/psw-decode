<?php
require_once 'highlight.inc.php';
$storageoffset = 0;
if(isset($_POST['userstoragestart'])) {
    $storageoffset = hexdec($_POST['userstoragestart']);
}
if(isset($_POST['userstorage'])) {
    $userstorageclean = str_replace(' ', '', preg_replace('/^000\d\d\d\s+/m', '', $_POST['userstorage']));
    $userstoragenolines = str_replace(["\n", "\r"], '', $userstorageclean);
}
function print_psw($PSW) {
    $storageoffset = 0;
    if(isset($_POST['userstoragestart'])) {
        $storageoffset = hexdec($_POST['userstoragestart']);
    }
    if(isset($_POST['userstorage'])) {
        $userstorageclean = str_replace(' ', '', preg_replace('/^000\d\d\d\s+/m', '', $_POST['userstorage']));
        $userstoragenolines = str_replace(["\n", "\r"], '', $userstorageclean);
    }
    if(strlen($PSW) != 16) {
        echo "Error: Provided string is not 16 characters.<br>\n";
        return;
    }
    if(!ctype_xdigit($PSW)) {
        echo "Error: Provided string is not all hex characters.<br>\n";
        return;
    }
    $firsttwobytes = substr($PSW,0,4);
    $interruptcode = substr($PSW,4,4);
    $interruptcodedec = hexdec($interruptcode);
    $interruptcodehex = dechex($interruptcodedec);
    $ninthnibble = substr($PSW,8,1);
    $ninthnibbledec = hexdec($ninthnibble);
    $ilc = ($ninthnibbledec & 12) >> 2;
    $ilcbinstr = str_pad(decbin($ilc), 2, '0', STR_PAD_LEFT);
    $abending_instruction_length = $ilc * 2;
    $condition_code = $ninthnibbledec & 3;
    $condition_code_binstr = str_pad(decbin($condition_code), 2, '0', STR_PAD_LEFT);
    $tenthnibble = substr($PSW,9,1);
    $nextaddress = substr($PSW,10);
    $address_of_abending_instruction = str_pad(dechex(hexdec($nextaddress) - $abending_instruction_length), 6, '0', STR_PAD_LEFT);
    $program_interrupts = [
        '',
        'Operation exception',
        'Privileged operation exception',
        'Execute exception',
        'Protection exception|Segment translation exception|Page translation exception',
        'Addressing exception',
        'Specification exception',
        'Data exception',
        'Fixed-point overflow exception',
        'Fixed-point divide exception',
        'Decimal overflow exception',
        'Decimal divide exception',
        'Exponent overflow exception',
        'Exponent underflow exception',
        'Significance exception',
        'Floating-point divide exception'
    ];
    $instructionmessage = "";
    if(!empty($userstoragenolines)) {
        $abendinginstruction = substr($userstoragenolines, 2 * ($storageoffset + hexdec($address_of_abending_instruction)), 2 * $abending_instruction_length);
        if(!empty($abendinginstruction)) $instructionmessage = <<<HTML
                <li>
                    The instruction that caused the abend was <span class="hex">$abendinginstruction</span>.
                </li>
        HTML;
    }
    echo <<<HTML
    <div class="psw">
        <span class="firsttwobytes">$firsttwobytes</span><span class="interruptcode">$interruptcode</span> <span class="ninthnibble">$ninthnibble</span><span class="tenthnibble">$tenthnibble</span><span class="nextaddress">$nextaddress</span>
    </div>
    <div class="decoded">
        <ul>
            <li>
                The interrupt code is <span class="interruptcode">$interruptcode</span> which is a SOC{$interruptcodehex}: {$program_interrupts[$interruptcodedec]}
            </li>
            <li>
                The ILC is <span class="ninthnibble">$ilcbinstr</span> (binary) or <span class="ilc">$ilc</span> (decimal), so the ABENDing instruction is<br>
                <span class="ilc">$ilc</span> * 2 = <span class="instructionlength">$abending_instruction_length</span> bytes long.
            </li>
            <li>
                The CC is set at <span class="ninthnibble">$condition_code_binstr</span> (binary) or <span class="ninthnibble">$condition_code</span> (decimal).
            </li>
            <li>
                The address of the next instruction is <span class="nextaddress">$nextaddress</span>.
            </li>
            <li>
                The address of the ABENDing instruction is <span class="nextaddress">$nextaddress</span> - <span class="instructionlength">$abending_instruction_length</span> = <span class="abendingaddress">$address_of_abending_instruction</span>
            </li>
            $instructionmessage
        </ul> 
    </div>
    HTML;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>PSW Decode</title>
    <meta name content="Decode a PSW from hex">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PSW Decoder" />
    <meta property="og:url" content="https://psw-decode.chigeek.xyz/">
    <meta property="og:title" content="PSW Decoder">
    <meta property="og:description" content="Decode a PSW from hex">
    <meta property="og:image" content="https://psw-decode.chigeek.xyz/screenshots.gif">
    <meta property="og:image:alt" content="PSW Decoder" />
    <meta name="twitter:image:src" content="https://psw-decode.chigeek.xyz/screenshots.gif">
    <meta name="twitter:site" content="@chigeekgreg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="PSW Decoder" />
    <meta name="twitter:description" content="Decode a PSW from hex" />
    <link rel="stylesheet" href="style.css">
    <style>
    .psw, .decoded {
        font-size: 2em;
    }
    .interruptcode {
        color: #5894ed;
    }
    .ninthnibble {
        color: red;
    }
    .decoded span, .psw span, .hex {
        font-family: monospace, monospace;
    }
    .ilc {
        color: goldenrod;
    }
    .instructionlength {
        color: magenta;
    }
    .nextaddress {
        color: lightgreen;
    }
    .abendingaddress {
    }
    </style>
</head>
<body>
    <h1>PSW Decode</h1>
    <hr>
    <h2>Instructions</h2>
    <p>Enter the PSW and click on the Decode button.</p>
    <p>Optionally, also supply a snippet of the relevant program memory as hex bytes. If the snippet does not start at address 0, then also provide the address that the snippet was copied from.</p>
    <hr>
    <form id="pswform" method="POST">
        <label for="psw">PSW:</label>
        <input type=text id="psw" name="psw"<?php if(isset($_POST['psw'])) echo ' value="' . $_POST["psw"] . '"'; ?>> <input type="submit" value="Decode"><br>
        <label for="userstoragestart">User Storage Address Begins:</label>
        <input type="text" id="userstoragestart" name="userstoragestart"<?php if(isset($_POST['userstoragestart'])) echo ' value="' . $_POST["userstoragestart"] . '"'; ?>><br>
        <label for="userstorage">User Storage:</label><br>
        <textarea id="userstorage" name="userstorage" rows="4" cols="80"><?php if(isset($userstorageclean)) echo $userstorageclean; ?></textarea>
    </form>
    <p id="pswdecode">
<?php if(isset($_POST['psw'])) print_psw(str_replace(' ', '', $_POST["psw"])); else echo "<hr>Screenshots:<br><img src=\"screenshots.gif\" style=\"object-fit: none; object-position: -110px; width: 960px; height:500px\">\n"; ?>
    </p>
    <div class="container text-center" style="font-size: small; text-align: center">
        <a href="?source" alt="View source" title="View source">View source</a>
    </div>
</body>
</html>