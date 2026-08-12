<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="images/logo.ico">
    <link rel="manifest" href="manifest.json">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
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

    <script src="https://cdn.jsdelivr.net/npm/monaco-editor/min/vs/loader.js"></script>
    <script>
        require.config({
            paths: {
                vs: "https://cdn.jsdelivr.net/npm/monaco-editor/min/vs"
            },
        });
    </script>
</body>

</html>