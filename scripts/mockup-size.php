<?php
$i = getimagesize(__DIR__ . '/../public/images/home/mockup-full.png');
echo $i[0] . 'x' . $i[1] . PHP_EOL;
