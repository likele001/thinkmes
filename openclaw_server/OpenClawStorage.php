<?php
declare(strict_types=1);

class OpenClawStorage
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = rtrim($dir, '/');
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0777, true);
        }
    }

    public function append(string $name, array $row): void
    {
        $file = $this->file($name);
        $data = $this->readArray($file);
        $data[] = $row;
        $this->writeArray($file, $data);
    }

    public function upsertBy(string $name, array $row, array $keys): void
    {
        $file = $this->file($name);
        $data = $this->readArray($file);
        $idx = -1;
        foreach ($data as $i => $it) {
            if (!is_array($it)) continue;
            $match = true;
            foreach ($keys as $k) {
                if (($it[$k] ?? null) !== ($row[$k] ?? null)) { $match = false; break; }
            }
            if ($match) { $idx = (int) $i; break; }
        }
        if ($idx >= 0) $data[$idx] = array_merge($data[$idx], $row);
        else $data[] = $row;
        $this->writeArray($file, $data);
    }

    public function getAll(string $name): array
    {
        return $this->readArray($this->file($name));
    }

    private function file(string $name): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\\-\\.]/', '_', $name);
        return $this->dir . '/' . $safe . '.json';
    }

    private function readArray(string $file): array
    {
        if (!is_file($file)) return [];
        $raw = (string) @file_get_contents($file);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function writeArray(string $file, array $data): void
    {
        $tmp = $file . '.' . getmypid() . '.tmp';
        @file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
        @rename($tmp, $file);
    }
}

