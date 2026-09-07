<?php
$img = imagecreatefrompng('D:\xampp\htdocs\rockettcg\assets\Rocket_foto_de_perfil_png.png');
$colors = [];
$w = imagesx($img);
$h = imagesy($img);
for($x=0;$x<$w;$x+=10){
    for($y=0;$y<$h;$y+=10){
        $rgb = imagecolorat($img, $x, $y);
        $colors[] = sprintf('%06X', $rgb & 0xFFFFFF);
    }
}
$counts = array_count_values($colors);
arsort($counts);
print_r(array_slice($counts, 0, 10));
?>
