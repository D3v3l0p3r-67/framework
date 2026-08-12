<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

function processFormXml($formXml, $formKind)
{
    libxml_use_internal_errors(true); // Suppress XML parsing errors

    // Load the XML into DOMDocument
    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = true; // Preserve original formatting
    $dom->formatOutput = false; // Avoid automatic formatting changes
    $dom->loadHTML($formXml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    if ($dom === false) {
        echo "Failed to parse XML.\n";
        foreach (libxml_get_errors() as $error) {
            echo "Error [Line {$error->line}, Column {$error->column}]: {$error->message}\n";
        }
        libxml_clear_errors();
        return null; // Return null if XML loading fails
    }

    // XPath to find elements with the 'visible' attribute
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//*[@visible]');

    // Iterate over the nodes in reverse order to avoid issues with modifying the tree while iterating
    for ($i = $nodes->length - 1; $i >= 0; $i--) {
        $node = $nodes->item($i);
        $visibleAttr = $node->getAttribute('visible');
        if (preg_match('/ApfServer\.IsFormKind\((.*?)\)/', $visibleAttr, $matches)) {
            $requiredKind = $matches[1];
            if ($requiredKind !== $formKind) {
                // If the form kind does not match, remove the entire element
                $node->parentNode->removeChild($node);
            }
        } else {
            // If the 'visible' attribute format is invalid, remove the entire element
            $node->parentNode->removeChild($node);
        }
    }

    // Check if the body element exists and manually concatenate the child nodes' HTML
    $body = $dom->getElementsByTagName('body')->item(0);
    if ($body !== null) {
        $processedHtml = '';
        foreach ($body->childNodes as $child) {
            $processedHtml .= $dom->saveHTML($child);
        }
    } else {
        // If there's no body, return the entire document's HTML as a fallback
        $processedHtml = $dom->saveHTML();
    }

    return $processedHtml;
}



// Example usage
$formXml = '<nav class="sticky-toolbar">
    <div class="nav-wrapper" id="main-toolbar">
        <a href="#" data-target="slide-out" class="sidenav-trigger white-text">
            <i class="material-icons">menu</i>
        </a>
        <span class="toolbar-title white-text fancy-text-shadow">
            ApdType.[ApfServer.FormKind]
        </span>
        <ul class="right menu">
            <li visible="ApfServer.IsFormKind(Show)">
                <a href="internal:ApdType.Edit?id={{id}}">
                    <i class="material-icons white-text">edit</i>
                </a>
            </li>
           <li visible="ApfServer.IsFormKind(New)">
                <a href="internal:ApdType.InsertAndShowGrid?form-id=ApdType-Show">
                    <i class="material-icons white-text">save</i>
                </a>
            </li>
            <li visible="ApfServer.IsFormKind(Duplicate)">
                <a href="internal:ApdType.InsertAndShowGrid?form-id=ApdType-Show">
                    <i class="material-icons white-text">save</i>
                </a>
            </li>
            <li visible="ApfServer.IsFormKind(Edit)">
                <a href="internal:ApdType.UpdateAndShowGrid?form-id=ApdType-Show">
                    <i class="material-icons white-text">save</i>
                </a>
            </li>

            <!-- TODO: Add to ButtonList more -->
            <li visible="ApfServer.IsFormKind(Show)">
                <a href="internal:ApdType.Duplicate?id={{id}}">
                    <i class="material-icons white-text">content_copy</i>
                </a>
            </li>
            <li visible="ApfServer.IsFormKind(Show)">
               <a href="internal:ApdType.DeleteAndShowGrid?must-confirm=true&id={{id}}">
                  <i class="material-icons white-text">delete</i>
               </a>
           </li>

        </ul>
    </div>
</nav>
<form class="col s12" id="ApdType-Show">
    <input id="id" name="id" type="hidden" value="{{id}}" class="form-field">
    <div class="row">
        <div class="input-field col s12">
            <input id="name" name="name" type="text" class="form-field validate" value="{{name}}">
            <label for="name">Name</label>
        </div>
    </div>
    <div class="row">
        <div class="input-field col s12">
            <input id="table_name" name="table_name" type="text" class="form-field validate" value="{{table_name}}">
            <label for="table_name">TableName</label>
        </div>
    </div>
    <div class="row">
        <div class="input-field col s12">
            <input id="inherits_from" name="inherits_from" type="text" class="form-field validate" value="{{inherits_from}}">
            <label for="inherits_from">InheritsFrom</label>
        </div>
    </div>
</form>
<!-- TODO: CREATE DETAILS TABLE AND PUT IT THERE THAN INJECT DETAILS IN PROCESS FORM FOR FORMKIND = SHOW -->
<br/>
<div id="tab-component-static-type-show-{{random}}" class="tab-component-static" visible="ApfServer.IsFormKind(Show)">
    <div id="tab-container_buttons" class="tab-buttons sticky-tabs">
        <button class="btn tab-button active">
            Forms
        </button>
        <button class="btn tab-button">
            Details
        </button>
    </div>
    <div id="tab-container_content" class="tab-contents">
        <div class="tab-content">
            <include actionkey="ApdForm.DataGrid?type_id={{id}}" />
        </div>
        <div class="tab-content">
            <include actionkey="ApdDetail.DataGrid?type_id={{id}}" />
        </div>
    </div>
</div>';

$formKind = "Edit";
$result = processFormXml($formXml, $formKind);

echo "<br>Original XML:<br><pre>" . htmlspecialchars($formXml) . "</pre><br>";
echo "<br>Processed XML:<br><pre>" . htmlspecialchars($result) . "</pre><br>";
