<?php

declare(strict_types=1);

namespace Cekta\Framework;

readonly class FilePHP
{
    public function __construct(private string $filename)
    {
    }

    public function write(array $data, int $permission = 0777): void
    {
        $content = '<?php return ' . var_export($data, true) . ';';
        if (file_put_contents($this->filename, $content, LOCK_EX) === false) {
            throw new \RuntimeException("$this->filename cant be cached");
        }
        chmod($this->filename, $permission);
    }

    public function read(array $default = []): array
    {
        if (!is_file($this->filename)) {
            return $default;
        }
        $result = include $this->filename;
        if (!is_array($result)) {
            throw new \RuntimeException("$this->filename must contain array");
        }
        return $result;
    }
}
