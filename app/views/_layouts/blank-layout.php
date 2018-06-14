<?php

use TriTan\Functions as func;

ob_start();
ob_implicit_flush(0);

$this->section('blank');
$this->stop();

func\print_gzipped_page();
