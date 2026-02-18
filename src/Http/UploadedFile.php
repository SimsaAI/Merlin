<?php

namespace Merlin\Http;

class UploadedFile
{
    public function __construct(
        protected string $name,
        protected string $type,
        protected string $tmpName,
        protected int $error,
        protected int $size
    ) {
    }

    public function getClientFilename(): string
    {
        return $this->name;
    }

    public function getClientMediaType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function moveTo(string $targetPath): void
    {
        if (!$this->isValid()) {
            throw new \RuntimeException("Cannot move invalid uploaded file");
        }

        if (!move_uploaded_file($this->tmpName, $targetPath)) {
            throw new \RuntimeException("Failed to move uploaded file");
        }
    }
}
