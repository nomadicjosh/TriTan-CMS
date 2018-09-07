<?php

ob_start();
ob_implicit_flush(0);

$this->section('blank');
$this->stop();

print_gzipped_page();
