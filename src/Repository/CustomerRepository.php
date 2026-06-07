<?php
declare(strict_types=1);

final class CustomerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $street,
        string $city,
        string $zip,
    ): CustomerDTO {
        $stmt = $this->db->prepare(
            'INSERT INTO customers (first_name, last_name, email, phone, street, city, zip)
             VALUES (:first_name, :last_name, :email, :phone, :street, :city, :zip)'
        );
        $stmt->execute([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
            'street'     => $street,
            'city'       => $city,
            'zip'        => $zip,
        ]);
        $id = (int)$this->db->lastInsertId();
        return new CustomerDTO($id, $firstName, $lastName, $email, $phone, $street, $city, $zip);
    }

    public function getById(int $id): ?CustomerDTO
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? CustomerDTO::fromRow($row) : null;
    }
}
