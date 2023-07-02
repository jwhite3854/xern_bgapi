<?php

namespace Helium\services;

class Uploader
{
    public $file;
    public $filename;
    public $destination;
    public $extensions;
    public $permissions;
    public $tmp_filename;
    public $errors = [];

    public function __construct(array $uploadedFile, $destination = '', $extensions = [], $permissions = '777')
    {
        $this->file = $uploadedFile;
        $this->destination = $destination;
        $this->extensions = array_map('strtolower', $extensions);
        $this->permissions = octdec($permissions);

        $this->validate();
    }

    public function validate(): void
    {
        $this->filename = $this->tmp_filename = null;

        $checks = [
            ($this->file['tmp_name'] !== null),
            ($this->file['tmp_name'] != 'none'),
            (is_uploaded_file($this->file['tmp_name'])),
        ];

        if (in_array(false, $checks)) {
            $this->errors[] = "No file uploaded";
            return;
        }

        if (count($this->extensions) > 0) {
            $ext = $this->getExtension($this->file['name']);
            if (!in_array($ext, $this->extensions)) {
                $this->errors[] = "Filetype not allowed";
                return;
            }
        }

        if (!is_writeable($this->destination)) {
            $this->errors[] = "Destination is not writeable";
            if (!is_dir($this->destination)) {
                $this->errors[] = "Destination does not exist";
            }
            return;
        }

        $this->filename = $this->file['name'];
        $this->tmp_filename = $this->file['tmp_name'];
    }

    public function getExtension(string $filename): string
    {
        $parts = explode('.', $filename);
        $ext = array_pop($parts);

        return strtolower($ext);
    }

    public function save(): bool
    {
        $destination = rtrim($this->destination, '/') . '/';
        if (move_uploaded_file($this->file['tmp_name'], $destination . $this->filename)) {
            chmod($destination . $this->filename, $this->permissions);
            return true;
        }

        return false;
    }
}