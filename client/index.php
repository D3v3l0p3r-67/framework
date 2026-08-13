<?php
require_once __DIR__ . '/../server/core/Session.php';

$csrfToken = (new Framework\Core\Session())->getCsrfToken();
?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="icon" href="images/logo.ico">
    <link rel="manifest" href="manifest.json">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Security-Policy"
        content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; worker-src 'self' blob:;">
    <title>Falcon client</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css" media="screen,projection" />
    <link type="text/css" rel="stylesheet" href="css/theme.css" media="screen,projection" id="theme-css" />
    <!--
    <link type="text/css" rel="stylesheet" href="./vendor/monaco/vs/editor/editor.main.css" data-name="vs/editor/editor.main" />
    -->
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/mustache.min.js"></script>

    <link type="text/css" rel="stylesheet" href="css/main.css?v=<?php echo filemtime('css/main.css'); ?>" media="screen,projection" />
    <script type="text/javascript" src="js/client.js?v=<?php echo filemtime('js/client.js'); ?>"></script>
</head>

<body>
    <div id="menu-dropdown-container">
        <!-- drop downs - auto generate content -->
    </div>

    <!-- sidenav -->
    <aside>
        <div id="sidenav">
            <!-- dinamically populatated -->
        </div>
    </aside>

    <main>
        <!--
        <div id="container" class="monaco-editor">
            <test>
                <a href="">test 123</a>
            </test>
        </div>
        -->
        <div id="tab-container">
            <!-- content will be injected here! -->
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs/loader.js"></script>
    <script>
        require.config({
            paths: {
                vs: "https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs"
            },
        });
    </script>
</body>

</html>
