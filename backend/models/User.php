<?php
class User
{

  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function findByEmail(string $email): array|false
  {
    $sql = 'SELECT * FROM users WHERE email = :email LIMIT 1';
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    return $stmt->fetch();
  }

  public function register(string $name, string $email, string $password): bool
  {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'student')";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
      'name' => $name,
      'email' => $email,
      'password' => $hashedPassword,

    ]);
  }

  public function getAllUsers(): array
  {
    $sql = "SELECT id, name, email, role, is_active
            FROM users
            ORDER BY id DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function updateUserStatus(int $userId, bool $isActive): bool
  {
    $sql = "UPDATE users
            SET is_active = :is_active
            WHERE id = :id
            AND role != 'admin'";

    $stmt = $this->pdo->prepare($sql);

    $stmt->execute([
      'is_active' => (int) $isActive,
      'id' => $userId
    ]);

    return $stmt->rowCount() === 1;
  }

  public function getActiveAdvisors(): array
  {
    $sql = "SELECT id, name
            FROM users
            WHERE role = 'advisor'
            AND is_active = 1
            ORDER BY name ASC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
  }
}
