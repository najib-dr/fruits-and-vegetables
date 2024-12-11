<?php

namespace App\Application\Reader;

class FileReader
{
    public function read(string $path): string
    {
        return file_get_contents($path);
    }
}
