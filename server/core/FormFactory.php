<?php
require_once('./core/Database.php');
require_once('./core/Session.php');
require_once('./core/Logger.php');
require_once('./core/vendor/simplehtmldom/simple_html_dom.php');

use Framework\Core\Database;
use Framework\Core\Logger;
use simplehtmldom\HtmlDocument;

class FormFactory
{
    public static function GetFormTemplate($name): string
    {
        $db = new Database();
        $form = $db->getByFilter('ApdForm', ['name' => '"' . $name . '"']);

        $template = $form->definition ?? 'Form not found!';

        $template .= self::getDetailsForFormName($name);

        return $template;
    }

    public static function ProcessForm($formXml, $formKind, $dataInput = null, $dataOutput = null): array|string|null
    {
        $formXml = self::processApfServerIsFormKind($formXml, $formKind, $dataInput, $dataOutput);
        $formXml = self::processServerVariables($formXml, $formKind, $dataInput, $dataOutput);
        $formXml = self::processReadOnlyFormFields($formXml, $formKind, $dataInput, $dataOutput);

        return $formXml;
    }

    private static function getDetailsForFormName($name)
    {
        $db = new Database();
        $form = $db->getByFilter('ApdForm', ['name' => '"' . $name . '"']);
        $masterTypeId = $form->id ?? 0;

        return self::getDetails($masterTypeId);
    }

    private static function getDetails($masterFormId)
    {
        $db = new Database();

        $details = $db->getAll('ApdDetail', 'position', ['form_id' => '"' . $masterFormId . '"']);

        $result = '<div id="tab-component-static-{{random}}" class="tab-component-static">';

        // Generate the tab buttons
        $result .= '<div id="tab-container_buttons" class="tab-buttons sticky-tabs">';

        foreach ($details as $index => $detail) {
            // First tab button should be active
            $activeClass = $index === 0 ? 'active' : '';
            $result .= '<button class="btn tab-button ' . $activeClass . '">' . $detail->name . '</button>';
        }

        $result .= '</div>';

        // Generate the tab contents
        $result .= '<div id="tab-container_content" class="tab-contents">';

        foreach ($details as $index => $detail) {
            // First tab content should be shown by default
            $activeClass = $index === 0 ? 'active' : '';
            $result .= '<div class="tab-content ' . $activeClass . '">';
            $result .= $detail->definition;  // Insert the <include> definition
            $result .= '</div>';
        }

        $result .= '</div>';  // Close tab-contents

        $result .= '</div>';  // Close tab-component-static

        return $result;
    }

    private static function processServerVariables($formXml, $formKind, $dataInput = null, $dataOutput = null)
    {
        $formXml = str_replace('[ApfServer.FormKind]', $formKind, $formXml);

        return $formXml;
    }

    private static function processReadOnlyFormFields($formXml, $formKind, $dataInput = null, $dataOutput = null)
    {
        if ($formKind === "Show") {
            $pattern = '/<(\w+)([^>]*\bclass="[^"]*\bform-field\b[^>]*)>/i';

            $formXml = preg_replace_callback($pattern, function ($matches) {
                $tagName = $matches[1];
                $attributes = $matches[2];

                if (strpos($attributes, 'disabled') === false) {
                    $attributes .= ' disabled';
                }

                return "<$tagName$attributes>";
            }, $formXml);

            $formXml = str_replace('data-readonly="false"', 'data-readonly="true"', $formXml);
        }
        return $formXml;
    }
    private static function processApfServerIsFormKind($formXml, $formKind, $dataInput = null, $dataOutput = null)
    {
        // Load the HTML content into simple-html-dom
        $dom = new HtmlDocument();
        $dom->load($formXml);

        // Find all elements with the 'visible' attribute
        foreach ($dom->find('[visible]') as $element) {
            $visibleAttr = $element->getAttribute('visible');
            if (preg_match('/ApfServer\.IsFormKind\((.*?)\)/', $visibleAttr, $matches)) {
                $requiredKind = $matches[1];
                if ($requiredKind !== $formKind) {
                    // If the form kind does not match, remove the entire element
                    $element->outertext = '';
                } else {
                    // If the form kind matches, remove the 'visible' attribute
                    $element->removeAttribute('visible');
                }
            } else {
                // If the 'visible' attribute format is invalid, remove the entire element
                $element->outertext = '';
            }
        }

        // Return the modified HTML
        return $dom->save();
    }
    /*
    private static function processApfServerIsFormKind($formXml, $formKind, $dataInput = null, $dataOutput = null)
    {
        $pattern = '/<(\w+)([^>]*?)\s+visible="ApfServer\.IsFormKind\((.*?)\)"(.*?)>(.*?)<\/\1>/s';

        $processedXml = preg_replace_callback($pattern, function ($matches) use ($formKind) {
            $tagName = $matches[1];
            $attributesBefore = $matches[2];
            $requiredKind = $matches[3];
            $attributesAfter = $matches[4];
            $content = $matches[5];

            if ($requiredKind === $formKind) {
                return "<$tagName$attributesBefore$attributesAfter>$content</$tagName>";
            } else {
                return ""; //<!-- <$tagName$attributesBefore$attributesAfter>$content</$tagName> -->";
            }
        }, $formXml);

        return $processedXml;
    }
    */
}
