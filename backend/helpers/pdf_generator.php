<?php

/**
 * Génère un PDF simple (texte) contenant les informations de commande
 * et retourne le chemin du fichier généré.
 */
function generateOrderPdf(array $order, array $items, array $user, array $shipping, string $destinationPath): string
{
    $lines = [
        ['text' => 'E-commerce YNOV', 'size' => 20, 'align' => 'center', 'spacing' => 30],
        ['text' => 'Reçu de commande #' . $order['id'], 'size' => 16, 'align' => 'center', 'spacing' => 24],
        ['text' => 'Date : ' . $order['date'], 'size' => 12, 'align' => 'center', 'spacing' => 24],
        ['text' => '', 'size' => 12, 'spacing' => 22],
        ['text' => 'Informations client', 'size' => 14, 'align' => 'left', 'bold' => true],
        ['text' => 'Nom : ' . trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')), 'size' => 12],
        ['text' => 'Email : ' . ($user['email'] ?? ''), 'size' => 12],
        ['text' => 'Téléphone : ' . ($user['telephone'] ?? 'Non communiqué'), 'size' => 12],
        ['text' => 'Solde restant : ' . number_format($order['remaining_balance'], 2) . ' €', 'size' => 12],
    ];

    $addressParts = array_filter([
        $shipping['address'] ?? '',
        $shipping['city'] ?? '',
        $shipping['postal'] ?? ''
    ]);
    if (!empty($addressParts)) {
        $lines[] = ['text' => 'Adresse de livraison : ' . implode(', ', $addressParts), 'size' => 12];
    }

    $lines[] = ['text' => '', 'size' => 12, 'spacing' => 20];
    $lines[] = ['text' => 'Articles commandés', 'size' => 14, 'bold' => true, 'underline' => true];

    foreach ($items as $item) {
        $lines[] = [
            'text' => sprintf('%s x %d - %.2f €', $item['name'], $item['quantity'], $item['line_total']),
            'size' => 12
        ];
    }

    $lines[] = ['text' => '', 'size' => 12, 'spacing' => 20];
    $lines[] = ['text' => sprintf('Total payé : %.2f €', $order['total']), 'size' => 14, 'bold' => true];

    simplePdfCreate($lines, $destinationPath);
    return $destinationPath;
}

/**
 * Écrit un PDF minimaliste (texte) depuis un tableau de lignes.
 */
function simplePdfCreate(array $lines, string $path): void
{
    $textStream = buildPdfTextStream($lines);

    $objects = [
        "<< /Type /Catalog /Pages 2 0 R >>",
        "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
        "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>",
        "<< /Length " . strlen($textStream) . " >>\nstream\n" . $textStream . "\nendstream",
        "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>",
    ];

    $pdfContent = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $index => $objectBody) {
        $offsets[$index + 1] = strlen($pdfContent);
        $pdfContent .= ($index + 1) . " 0 obj\n" . $objectBody . "\nendobj\n";
    }

    $xrefPosition = strlen($pdfContent);
    $pdfContent .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdfContent .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdfContent .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdfContent .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdfContent .= "startxref\n" . $xrefPosition . "\n%%EOF";

    file_put_contents($path, $pdfContent);
}

function buildPdfTextStream(array $lines): string
{
    $stream = "BT\n/F1 12 Tf\n72 760 Td\n";
    foreach ($lines as $index => $line) {
        $text = pdfEscapeText($line['text'] ?? '');
        $fontSize = (float)($line['size'] ?? 12);
        $spacing = (float)($line['spacing'] ?? 18);
        $align = $line['align'] ?? 'left';

        $stream .= sprintf("/F1 %.1f Tf\n", $fontSize);

        if (!empty($line['bold'])) {
            $stream .= "0 Tr\n"; // normal text rendering
        }

        if (!empty($line['underline'])) {
            $stream .= "0 Tc\n"; // ensure no extra char spacing
        }

        $xPosition = 0;
        if ($align === 'center') {
            $textWidth = strlen($text) * ($fontSize * 0.6);
            $xPosition = max(0, (612 - $textWidth) / 2 - 72);
        }

        $stream .= sprintf("%d 0 Td\n", $xPosition);
        $stream .= "(" . $text . ") Tj\n";

        if (!empty($line['underline'])) {
            $stream .= "0 Tr\n";
        }

        if ($index < count($lines) - 1) {
            $stream .= sprintf("%d %.1f Td\n", -$xPosition, -$spacing);
        }
    }
    $stream .= "ET";
    return $stream;
}

function pdfEscapeText(string $text): string
{
    $converted = $text;

    if (function_exists('iconv')) {
        $result = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($result !== false) {
            $converted = $result;
        }
    } elseif (function_exists('mb_convert_encoding')) {
        $result = @mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
        if ($result !== false) {
            $converted = $result;
        }
    } else {
        $converted = preg_replace('/[^\x20-\x7E]/u', '?', $text);
    }

    return str_replace(
        ['\\', '(', ')'],
        ['\\\\', '\(', '\)'],
        $converted
    );
}

