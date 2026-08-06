<?php
require_once __DIR__ . '/config.php';

class Sqlite3PdoResult
{
    private SQLite3Result $result;

    public function __construct(SQLite3Result $result)
    {
        $this->result = $result;
    }

    public function fetchAll(): array
    {
        $rows = [];
        while ($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        $this->result->finalize();
        return $rows;
    }
}

class Sqlite3PdoStatement
{
    private SQLite3Stmt $stmt;
    private SQLite3 $db;
    private ?SQLite3Result $result = null;
    private int $lastChanges = 0;

    public function __construct(SQLite3 $db, string $sql)
    {
        $this->db = $db;
        $this->stmt = $db->prepare($sql);
        if ($this->stmt === false) {
            throw new RuntimeException($db->lastErrorMsg());
        }
    }

    public function bindValue($param, $value, $type = null): bool
    {
        $sqliteType = SQLITE3_TEXT;
        if ($type === PDO::PARAM_INT || is_int($value)) {
            $sqliteType = SQLITE3_INTEGER;
        }
        return $this->stmt->bindValue($param, $value, $sqliteType);
    }

    public function execute(?array $params = null)
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $this->bindValue($key, $value, $type);
            }
        }

        $this->result = $this->stmt->execute();
        if ($this->result === false) {
            throw new RuntimeException($this->db->lastErrorMsg());
        }

        $this->lastChanges = $this->db->changes();
        return $this;
    }

    public function fetch()
    {
        if ($this->result === null) {
            return false;
        }
        return $this->result->fetchArray(SQLITE3_ASSOC);
    }

    public function fetchAll(): array
    {
        if ($this->result === null) {
            return [];
        }
        $rows = [];
        while ($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        $this->result->finalize();
        return $rows;
    }

    public function rowCount(): int
    {
        return $this->lastChanges;
    }
}

class Sqlite3PdoAdapter
{
    private SQLite3 $db;

    public function __construct(string $sqlitePath)
    {
        $this->db = new SQLite3($sqlitePath);
    }

    public function exec(string $sql)
    {
        $result = $this->db->exec($sql);
        if ($result === false) {
            throw new RuntimeException($this->db->lastErrorMsg());
        }
        return $result;
    }

    public function prepare(string $sql)
    {
        return new Sqlite3PdoStatement($this->db, $sql);
    }

    public function query(string $sql)
    {
        $result = $this->db->query($sql);
        if ($result === false) {
            throw new RuntimeException($this->db->lastErrorMsg());
        }
        return new Sqlite3PdoResult($result);
    }

    public function beginTransaction(): void
    {
        $this->db->exec('BEGIN TRANSACTION');
    }

    public function commit(): void
    {
        $this->db->exec('COMMIT');
    }

    public function lastInsertId(): string
    {
        return (string) $this->db->lastInsertRowID();
    }

    public function setAttribute($attr, $value): void
    {
        // no-op for SQLite3 adapter
    }

    public function getAttribute($attr)
    {
        if ($attr === PDO::ATTR_DRIVER_NAME) {
            return 'sqlite';
        }
        return null;
    }
}

class Database
{
    private static $pdo = null;

    public static function getConnection()
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        if (DB_DRIVER === 'mysql' && extension_loaded('pdo_mysql')) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } elseif (extension_loaded('pdo_sqlite')) {
            $sqlitePath = DB_SQLITE_PATH;
            if (!file_exists(dirname($sqlitePath))) {
                mkdir(dirname($sqlitePath), 0755, true);
            }
            self::$pdo = new PDO('sqlite:' . $sqlitePath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } elseif (extension_loaded('sqlite3')) {
            $sqlitePath = DB_SQLITE_PATH;
            if (!file_exists(dirname($sqlitePath))) {
                mkdir(dirname($sqlitePath), 0755, true);
            }
            self::$pdo = new Sqlite3PdoAdapter($sqlitePath);
        } else {
            throw new RuntimeException('PDO SQLite driver is not installed. Enable pdo_sqlite in php.ini.');
        }

        self::initializeSchema();
        return self::$pdo;
    }

    private static function initializeSchema(): void
    {
        $pdo = self::$pdo;
        if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS certificate_requests (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    reference_no VARCHAR(32) NOT NULL UNIQUE,
                    full_name VARCHAR(255) NOT NULL,
                    designation VARCHAR(255) NOT NULL,
                    office VARCHAR(255) NOT NULL,
                    purpose TEXT NOT NULL,
                    request_date DATE NOT NULL,
                    status ENUM("Pending","Printed") NOT NULL DEFAULT "Pending",
                    created_at DATETIME NOT NULL,
                    printed_at DATETIME NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
            );
        } else {
            $pdo->exec(
                'CREATE TABLE IF NOT EXISTS certificate_requests (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    reference_no TEXT NOT NULL UNIQUE,
                    full_name TEXT NOT NULL,
                    designation TEXT NOT NULL,
                    office TEXT NOT NULL,
                    purpose TEXT NOT NULL,
                    request_date TEXT NOT NULL,
                    status TEXT NOT NULL DEFAULT "Pending",
                    created_at TEXT NOT NULL,
                    printed_at TEXT NULL
                )'
            );
        }
    }

    public static function saveApplication(string $fullName, string $designation, string $office, string $purpose, string $requestDate): array
    {
        $pdo = self::getConnection();
        $createdAt = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO certificate_requests (reference_no, full_name, designation, office, purpose, request_date, status, created_at)
             VALUES (:reference_no, :full_name, :designation, :office, :purpose, :request_date, "Pending", :created_at)'
        );

        $stmt->execute([
            ':reference_no' => '',
            ':full_name' => $fullName,
            ':designation' => $designation,
            ':office' => $office,
            ':purpose' => $purpose,
            ':request_date' => $requestDate,
            ':created_at' => $createdAt,
        ]);

        $id = (int) $pdo->lastInsertId();
        $referenceNo = self::formatReference($id);

        $stmt = $pdo->prepare('UPDATE certificate_requests SET reference_no = :reference_no WHERE id = :id');
        $stmt->execute([
            ':reference_no' => $referenceNo,
            ':id' => $id,
        ]);

        $pdo->commit();

        return ['id' => $id, 'reference_no' => $referenceNo];
    }

    public static function findDuplicate(string $fullName, string $designation, string $office, string $purpose, string $requestDate): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS count FROM certificate_requests
             WHERE LOWER(full_name) = LOWER(:full_name)
               AND LOWER(designation) = LOWER(:designation)
               AND LOWER(office) = LOWER(:office)
               AND LOWER(purpose) = LOWER(:purpose)
               AND request_date = :request_date
               AND created_at >= :created_since'
        );

        $stmt->execute([
            ':full_name' => $fullName,
            ':designation' => $designation,
            ':office' => $office,
            ':purpose' => $purpose,
            ':request_date' => $requestDate,
            ':created_since' => date('Y-m-d H:i:s', strtotime('-3 hours')),
        ]);

        $row = $stmt->fetch();
        return $row && ((int) $row['count'] > 0);
    }

    public static function getApplicationById(int $id): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM certificate_requests WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listApplications(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query('SELECT * FROM certificate_requests ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public static function deleteApplication(int $id): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('DELETE FROM certificate_requests WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function markAsPrinted(int $id): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('UPDATE certificate_requests SET status = "Printed", printed_at = :printed_at WHERE id = :id');
        $stmt->execute([
            ':printed_at' => date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function getStats(): array
    {
        $pdo = self::getConnection();
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');

        $stmt = $pdo->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = "Pending" THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = "Printed" THEN 1 ELSE 0 END) AS printed,
                SUM(CASE WHEN DATE(created_at) = :today THEN 1 ELSE 0 END) AS today,
                SUM(CASE WHEN DATE(created_at) >= :week_start THEN 1 ELSE 0 END) AS this_week,
                SUM(CASE WHEN DATE(created_at) >= :month_start THEN 1 ELSE 0 END) AS this_month
             FROM certificate_requests'
        );

        $stmt->execute([
            ':today' => $today,
            ':week_start' => $weekStart,
            ':month_start' => $monthStart,
        ]);

        $row = $stmt->fetch();
        return $row ?: [
            'total' => 0,
            'pending' => 0,
            'printed' => 0,
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
        ];
    }

    public static function getRecentApplications(int $limit = 5): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM certificate_requests ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private static function formatReference(int $id): string
    {
        return sprintf('CA-%s-%06d', date('Y'), $id);
    }
}
