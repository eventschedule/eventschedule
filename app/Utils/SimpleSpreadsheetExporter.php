<?php

namespace App\Utils;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SimpleSpreadsheetExporter
{
    /**
     * @param array<int, array<int, string|int|float|null>> $rows
     */
    public static function downloadCsv(array $rows, string $filename): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            foreach ($rows as $row) {
                fputcsv($handle, array_map(static function ($value) {
                    if ($value === null) {
                        return '';
                    }

                    return (string) $value;
                }, $row));
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="' . $filename . '"'
        );

        return $response;
    }

    /**
     * @param array<int, array<int, string|int|float|null>> $rows
     */
    public static function downloadXlsx(array $rows, string $filename, ?string $sheetTitle = null): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'export');

        if ($path === false) {
            abort(500, 'Unable to create temporary file.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            abort(500, 'Unable to create spreadsheet archive.');
        }

        $title = $sheetTitle ? self::sanitizeSheetTitle($sheetTitle) : 'Sheet1';

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml($title));
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheetXml($rows));

        $zip->close();

        $response = response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        return $response->deleteFileAfterSend(true);
    }

    protected static function sanitizeSheetTitle(string $title): string
    {
        $title = Str::of($title)
            ->replaceMatches('/[\[\]\*\?\\\/]/', '')
            ->trim()
            ->value();

        if ($title === '') {
            return 'Sheet1';
        }

        return mb_substr($title, 0, 31);
    }

    protected static function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    protected static function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    protected static function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    protected static function workbookXml(string $sheetTitle): string
    {
        $escapedTitle = htmlspecialchars($sheetTitle, ENT_XML1 | ENT_COMPAT, 'UTF-8');

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="{$escapedTitle}" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    /**
     * @param array<int, array<int, string|int|float|null>> $rows
     */
    protected static function sheetXml(array $rows): string
    {
        $xmlRows = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];

            foreach ($row as $columnIndex => $value) {
                $columnLetter = self::columnLetter($columnIndex + 1);
                $cellValue = htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_COMPAT, 'UTF-8');
                $cells[] = '<c r="' . $columnLetter . ($rowIndex + 1) . '" t="inlineStr"><is><t>'
                    . $cellValue
                    . '</t></is></c>';
            }

            $xmlRows[] = '<row r="' . ($rowIndex + 1) . '">' . implode('', $cells) . '</row>';
        }

        $sheetData = implode('', $xmlRows);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>{$sheetData}</sheetData>
</worksheet>
XML;
    }

    protected static function columnLetter(int $columnNumber): string
    {
        $letter = '';

        while ($columnNumber > 0) {
            $columnNumber--;
            $letter = chr(65 + ($columnNumber % 26)) . $letter;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $letter;
    }
}
