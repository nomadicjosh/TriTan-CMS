<?php

use TriTan\Functions\Core;

ob_start();
ob_implicit_flush(0);

$this->section('blank');
$this->stop();

Core\print_gzipped_page();
