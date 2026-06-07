<?php
declare(strict_types=1);

final class CustomerDTO
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phone,
        public string $street,
        public string $city,
        public string $zip,
    ) {}

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function getFullAddress(): string
    {
        return $this->street . ', ' . $this->zip . ' ' . $this->city;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id: (int)$row['id'],
            firstName: (string)$row['first_name'],
            lastName: (string)$row['last_name'],
            email: (string)$row['email'],
            phone: (string)$row['phone'],
            street: (string)$row['street'],
            city: (string)$row['city'],
            zip: (string)$row['zip'],
        );
    }
}