<?php
namespace TriTan\Interfaces;

interface FileSystemInterface
{
    public function mkdir(string $path);

    public function rmdir($dir);

    public function exists(string $filename);
}
