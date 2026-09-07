<?php
$files = glob("*.php");
$images = ['pokemonlogo.png', 'magiclogo.png', 'yugiohlogo.png', 'onepiecelogo.png'];

foreach ($files as $file) {
    if ($file === 'index.php' || $file === 'category.php') continue; // Já arrumados
    
    $content = file_get_contents($file);
    $modified = false;
    
    foreach ($images as $img) {
        $search1 = '<img src="assets/' . $img . '" alt="Pokémon">';
        $search2 = '<img src="assets/' . $img . '" alt="Magic">';
        $search3 = '<img src="assets/' . $img . '" alt="Yu-Gi-Oh!">';
        $search4 = '<img src="assets/' . $img . '" alt="One Piece">';
        
        $replace1 = '<img src="assets/' . $img . '" alt="Pokémon" style="height: 18px; width: auto; object-fit: contain;">';
        $replace2 = '<img src="assets/' . $img . '" alt="Magic" style="height: 18px; width: auto; object-fit: contain;">';
        $replace3 = '<img src="assets/' . $img . '" alt="Yu-Gi-Oh!" style="height: 18px; width: auto; object-fit: contain;">';
        $replace4 = '<img src="assets/' . $img . '" alt="One Piece" style="height: 18px; width: auto; object-fit: contain;">';
        
        if (strpos($content, $search1) !== false) {
            $content = str_replace($search1, $replace1, $content);
            $modified = true;
        }
        if (strpos($content, $search2) !== false) {
            $content = str_replace($search2, $replace2, $content);
            $modified = true;
        }
        if (strpos($content, $search3) !== false) {
            $content = str_replace($search3, $replace3, $content);
            $modified = true;
        }
        if (strpos($content, $search4) !== false) {
            $content = str_replace($search4, $replace4, $content);
            $modified = true;
        }
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "Replaced in $file\n";
    }
}
?>
