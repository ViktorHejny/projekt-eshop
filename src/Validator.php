<?php
declare(strict_types=1);

final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    public function required(string $field, mixed $value, string $message = 'Pole je povinné.'): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        if (trim((string)$value) === '') {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function email(string $field, mixed $value, string $message = 'Neplatný formát e-mailu.'): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        $v = trim((string)$value);
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function minLength(string $field, mixed $value, int $min, string $message = ''): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        $v = (string)$value;
        if ($v !== '' && mb_strlen($v) < $min) {
            $this->errors[$field] = $message !== '' ? $message : "Minimální délka je $min znaků.";
        }
        return $this;
    }

    public function maxLength(string $field, mixed $value, int $max, string $message = ''): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        if (mb_strlen((string)$value) > $max) {
            $this->errors[$field] = $message !== '' ? $message : "Maximální délka je $max znaků.";
        }
        return $this;
    }

    public function pattern(string $field, mixed $value, string $regex, string $message = 'Neplatný formát.'): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        $v = (string)$value;
        if ($v !== '' && !preg_match($regex, $v)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * @param array<int,mixed> $allowed
     */
    public function in(string $field, mixed $value, array $allowed, string $message = 'Neplatná hodnota.'): self
    {
        if (isset($this->errors[$field])) {
            return $this;
        }
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string,string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}
