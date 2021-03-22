<?php

declare(strict_types=1);

define('PHAR_NAME', 'TGUserbot.phar');
define('SRC_DIR', 'src');
if (trim(strtolower(readline('Run composer update? [y/n]: '))) === 'y') {
    passthru('cd ' . escapeshellarg(SRC_DIR) . ' && composer update');
}
$phar = new Phar(PHAR_NAME, 0, PHAR_NAME);
$phar->startBuffering();
$phar->buildFromDirectory(SRC_DIR);

echo "Changelog:\n";
touch(__DIR__ . '/changelog');
passthru('nano ' . escapeshellarg(__DIR__ . '/changelog'));
$changelog = trim(file_get_contents(__DIR__ . '/changelog'));
echo $changelog;
unlink(__DIR__ . '/changelog');
echo "\n\nEval:\n";
touch(__DIR__ . '/eval');
passthru('nano ' . escapeshellarg(__DIR__ . '/eval'));
$eval = trim(file_get_contents(__DIR__ . '/eval'));
echo $eval;
unlink(__DIR__ . '/eval');
echo "\n\nGit commit message:\n";
file_put_contents(__DIR__ . '/commitMessage', $changelog);
passthru('nano ' . escapeshellarg(__DIR__ . '/commitMessage'));
$commitMessage = trim(file_get_contents(__DIR__ . '/commitMessage'));
echo $commitMessage;
unlink(__DIR__ . '/commitMessage');

$phar->addFromString('.changelog', gzdeflate(json_encode(['changelog' => $changelog, 'eval' => $eval]), 9));
//$stub = "#!/usr/bin/env php \n".$phar->createDefaultStub('index.php');
$stub = $phar->createDefaultStub('index.php');
$phar->setStub($stub);
$phar->stopBuffering();

file_put_contents('info.txt', json_encode(['md5' => md5_file(PHAR_NAME)]));
echo "\n\nDone!\n";

if (trim(strtolower(readline('publish? [y/n]: '))) === 'y') {
    passthru('git add .');
    if ($commitMessage == '') {
        $commitMessage = '.';
    }
    passthru('git commit -m ' . escapeshellarg($commitMessage));
    passthru('git push');
}
