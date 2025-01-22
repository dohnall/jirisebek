<?php
exit;
$znak = "0123456789abcdef";

for($i = 0; $i < strlen($znak); $i++) {
	for($j = 0; $j < strlen($znak); $j++) {
		mkdir(substr($znak, $i, 1).substr($znak, $j, 1));
	}
}
