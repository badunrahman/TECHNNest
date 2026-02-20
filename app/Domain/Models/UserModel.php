<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Helpers\Core\PDOService;

class UserModel extends BaseModel
{
    private const TABLE = 'users';

    public function __construct(PDOService $db_service)
    {
        parent::__construct($db_service);
    }


    public function createUser(array $data): int
    {
         // TODO: Hash the password using password_hash() with PASSWORD_BCRYPT
          //       Store the result in $hashedPassword variable
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

          // TODO: Write an INSERT SQL query to insert a new user into the users table
        //       Insert: first_name, last_name, username, email, password_hash, role
        $sql = "
    INSERT INTO users (
    first_name,
    last_name,
    username,
    email,
    password_hash,
    role
    )
    VALUES (
    :first_name,
    :last_name,
    :username,
    :email,
    :password_hash,
    :role
    )
    ";
        //       Use named parameters (e.g., :first_name, :last_name, etc.)

         // TODO: Execute the query with appropriate parameters
        //       Use $hashedPassword for the password_hash field
        $parameters = [
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $hashedPassword,
            ':role' => $data['role'],
        ];

         // TODO: Return the last inserted ID
        $this->execute($sql, $parameters);

        return (int) $this->lastInsertId();
    }




    /**
     * Find a user by email address.
     *
     * @param string $email The email address to search for
     * @return array|null User data array or null if not found
     */
    public function findByEmail(string $email): ?array
    {
        // TODO: Write a SELECT SQL query to find a user by email
        //       Select all columns from the users table where email matches
        //       Use named parameter :email and LIMIT 1
        $sql = "
    SELECT * FROM users
    WHERE email = :email
    LIMIT 1
    ";

        // TODO: Execute the query and return the result
        $row = $this->selectOne($sql, [':email' => $email]);
    return ($row === false) ? null : $row;
    }


    /**
     * Find a user by username.
     *
     * @param string $username The username to search for
     * @return array|null User data array or null if not found
     */
    public function findByUsername(string $username): ?array
    {
        // TODO: Write a SELECT SQL query to find a user by username
        //       Select all columns from the users table where username matches
        //       Use named parameter :username and LIMIT 1

        $sql = "
    SELECT * FROM users
    WHERE username = :username
    LIMIT 1
    ";

        // TODO: Execute the query and return the result
         $row = $this->selectOne($sql, [':username' => $username]);
    return ($row === false) ? null : $row;;
    }



    /**
     * Check if an email address already exists in the database.
     *
     * @param string $email The email address to check
     * @return bool True if email exists, false otherwise
     */
    public function emailExists(string $email): bool
    {
        // TODO: Write a SELECT COUNT(*) query to count users with the given email
        //       Alias the count as 'count'
        //       Use named parameter :email
        $sql = "
    SELECT COUNT(*) AS count
    FROM users
    WHERE email = :email
    ";

        // TODO: Execute the query and return true if count > 0, false otherwise

        $result = $this->selectOne($sql, [':email' => $email]);

        if ($result && $result['count'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * Check if a username already exists in the database.
     *
     * @param string $username The username to check
     * @return bool True if username exists, false otherwise
     */
    public function usernameExists(string $username): bool
    {
        // TODO: Write a SELECT COUNT(*) query to count users with the given username
        //       Alias the count as 'count'
        //       Use named parameter :username

        $sql = "
    SELECT COUNT(*) AS count
    FROM users
    WHERE username = :username
    ";
        // TODO: Execute the query and return true if count > 0, false otherwise
        $result = $this->selectOne($sql, [':username' => $username]);

        if ($result && $result['count'] > 0) {
            return true;
        }

        return false;
    }

    /**
     * Verify user credentials by email/username and password.
     *
     * @param string $identifier Email or username
     * @param string $password  Plain-text password to verify
     * @return array|null       User data if credentials are valid, null otherwise
     */
    public function verifyCredentials(string $identifier, string $password): ?array
    {
        // TODO: Try to find user by email first
        $user = $this->findByEmail($identifier);

        // TODO: If user not found by email, try finding by username
        if (!$user) {
            $user = $this->findByUsername($identifier);
        }

        // TODO: If user still not found, return null (invalid credentials)

        // TODO: Verify the password using password_verify($password, $user['password_hash'])
        //       If password is valid, return $user
        //       If password is invalid, return null

        if (!$user) {
            return null;
        }

        // Verifying password against stored hash
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        // All good
        return $user;
    }
}
