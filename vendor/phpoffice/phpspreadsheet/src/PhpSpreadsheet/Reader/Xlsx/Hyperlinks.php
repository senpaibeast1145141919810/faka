<?php

namespace PhpOffice\PhpSpreadsheet\Reader\Xlsx;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;

class Hyperlinks
{
    private $worksheet;

    private $hyperlinks = [];

    public function __construct(Worksheet $workSheet)
    {
        $this->worksheet = $workSheet;
    }

    public function readHyperlinks(SimpleXMLElement $relsWorksheet): void
    {
        foreach ($relsWorksheet->children(Namespaces::RELATIONSHIPS)->Relationship as $elementx) {
            $element = Xlsx::getAttributes($elementx);
            if ($element->Type == Namespaces::HYPERLINK) {
                $this->hyperlinks[(string) $element->Id] = (string) $element->Target;
            }
        }
    }

    public function setHyperlinks(SimpleXMLElement $worksheetXml): void
    {
        foreach ($worksheetXml->children(Namespaces::MAIN)->hyperlink as $hyperlink) {
            if ($hyperlink !== null) {
                $this->setHyperlink($hyperlink, $this->worksheet);
            }
        }
    }

    private function setHyperlink(SimpleXMLElement $hyperlink, Worksheet $worksheet): void
    {
        // Link url
        $linkRel = Xlsx::getAttributes($hyperlink, Namespaces::SCHEMA_OFFICE_DOCUMENT);

        $attributes = Xlsx::getAttributes($hyperlink);
        foreach (Coordinate::extractAllCellReferencesInRange($attributes->ref) as $cellReference) {
            $cell = $worksheet->getCell($cellReference);
            if (isset($linkRel['id'])) {
                $hyperlinkUrl = $this->hyperlinks[(string) $linkRel['id']] ?? null;
                if (isset($hyperlink['location'])) {
                    $hyperlinkUrl .= '#' . (string) $hyperlink['location'];
                }
                $cell->getHyperlink()->setUrl($hyperlinkUrl);
            } elseif (isset($hyperlink['location'])) {
                $cell->getHyperlink()->setUrl('sheet://' . (string) $hyperlink['location']);
            }

            // Tooltip
            if (isset($hyperlink['tooltip'])) {
                $cell->getHyperlink()->setTooltip((string) $hyperlink['tooltip']);
            }
        }
    }
}
