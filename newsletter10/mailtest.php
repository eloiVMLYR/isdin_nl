<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$to = "eloi.vaque@vml.com";
$to = "eloiv@lliures.cat";
$subject = "Prueba Newsletter Antiarrugas ISDIN";
$from = "eloiv@lliures.cat";
//$from = "eloi.vaque@vml.com";
$fromName = "Eloi Vaqué";

// Carreguem l'HTML
$htmlFile = "newsletter3.html";
if (!file_exists($htmlFile)) {
    die("Error: no es troba el fitxer HTML $htmlFile");
}
$html = file_get_contents($htmlFile);

// Regex per trobar totes les imatges del HTML
preg_match_all('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
$images = $matches[1]; // array amb tots els src

$boundary = md5(time());
$headers = "MIME-Version: 1.0\r\n";
$headers .= "From: $fromName <$from>\r\n";
$headers .= "Content-Type: multipart/related; boundary=\"$boundary\"\r\n";

// Cos del correu amb HTML
$body = "--$boundary\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";

// Substituïm tots els src per CID i preparem les imatges
$cids = [];
foreach ($images as $img) {
    if (!file_exists($img)) {
        echo "Atenció: no es troba la imatge $img, serà ignorada.\n";
        continue;
    }

    // Si la imatge ja té CID, reutilitzem-lo
    if (!isset($cids[$img])) {
        $cid = "img_" . md5($img); // CID únic per nom de fitxer
        $cids[$img] = $cid;
    } else {
        $cid = $cids[$img];
    }

    // Substituïm totes les aparicions al HTML per aquest CID
    $html = str_replace('src="' . $img . '"', 'src="cid:' . $cid . '"', $html);
}

// Afegim l'HTML final
$body .= $html . "\r\n\r\n";

// Afegim les imatges
foreach ($cids as $imgFile => $cid) {
    $data = chunk_split(base64_encode(file_get_contents($imgFile)));
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: image/" . pathinfo($imgFile, PATHINFO_EXTENSION) . "; name=\"" . basename($imgFile) . "\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-ID: <$cid>\r\n";
    $body .= "Content-Disposition: inline; filename=\"" . basename($imgFile) . "\"\r\n\r\n";
    $body .= $data . "\r\n\r\n";
}

$body .= "--$boundary--";

// DEBUG: mostrem headers i cos parcial
echo "<pre>HEADERS:\n$headers\n\nBODY (primer 1000 caràcters):\n" . substr($body, 0, 1000) . "\n...\n</pre>";

// Enviem el correu
if(mail($to, $subject, $body, $headers)){
    echo "Correu enviat correctament!";
} else {
    echo "Error enviant el correu.\n";
    print_r(error_get_last());
}