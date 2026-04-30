<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;

/**
 * Veritabanı (Database) Singleton Sınıfı.
 * Dosya adı: database.php (küçük harf) — Linux/Render ile Git büyük-küçük harf uyumu için tek kaynak.
 */
class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private string $port;
    private string $sslmode;
    private ?PDO $conn = null;

    private static ?Database $instance = null;

    private static function loadEnvFromFile(): void {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (!is_readable($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $len = strlen($value);
            if ($len >= 2 && $value[0] === '"' && $value[$len - 1] === '"') {
                $value = substr($value, 1, -1);
            } elseif ($len >= 2 && $value[0] === "'" && $value[$len - 1] === "'") {
                $value = substr($value, 1, -1);
            }
            if ($name !== '' && getenv($name) === false) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * @return array{host:string,port:string,dbname:string,user:string,password:string,sslmode:string}|null
     */
    private static function parseDatabaseUrl(string $url): ?array {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return null;
        }
        $scheme = $parts['scheme'] ?? '';
        if ($scheme !== 'postgresql' && $scheme !== 'postgres') {
            return null;
        }
        $sslmode = 'require';
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $q);
            if (!empty($q['sslmode'])) {
                $sslmode = (string) $q['sslmode'];
            }
        }
        $path = $parts['path'] ?? '/postgres';
        $dbname = ltrim($path, '/');
        if ($dbname === '') {
            $dbname = 'postgres';
        }

        return [
            'host' => $parts['host'],
            'port' => isset($parts['port']) ? (string) $parts['port'] : '5432',
            'dbname' => $dbname,
            'user' => $parts['user'] ?? 'postgres',
            'password' => $parts['pass'] ?? '',
            'sslmode' => $sslmode,
        ];
    }

    /**
     * Render/Docker gibi IPv6’sız ortamlarda AAAA önce gelince bağlantı düşer.
     * libpq: host= (SNI/şifreleme için) + hostaddr=IPv4 (gerçek TCP hedefi).
     *
     * @return array{0:?string,1:string} [ hostaddr or null, host for DSN ]
     */
    private static function resolvePgsqlHostForDsn(string $host): array {
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return [null, $host];
        }
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return [null, $host];
        }
        $manual = getenv('DB_HOSTADDR');
        if (is_string($manual) && $manual !== ''
            && filter_var($manual, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return [$manual, $host];
        }
        $records = @dns_get_record($host, DNS_A);
        if (is_array($records)) {
            foreach ($records as $rec) {
                if (($rec['type'] ?? '') === 'A' && !empty($rec['ip'])
                    && filter_var($rec['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return [$rec['ip'], $host];
                }
            }
        }
        $legacy = @gethostbyname($host);
        if (is_string($legacy) && $legacy !== $host
            && filter_var($legacy, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return [$legacy, $host];
        }
        return [null, $host];
    }

    private function __construct() {
        self::loadEnvFromFile();

        $databaseUrl = getenv('DATABASE_URL');
        if (is_string($databaseUrl) && $databaseUrl !== '') {
            $parsed = self::parseDatabaseUrl($databaseUrl);
            if ($parsed === null) {
                die('Veritabanı: DATABASE_URL geçersiz veya okunamadı.');
            }
            $this->host = $parsed['host'];
            $this->port = $parsed['port'];
            $this->db_name = $parsed['dbname'];
            $this->username = $parsed['user'];
            $this->password = $parsed['password'];
            $this->sslmode = $parsed['sslmode'];
        } else {
            $this->host = getenv('DB_HOST') ?: 'db.yzrfgshzdnicenrqxdum.supabase.co';
            $this->port = getenv('DB_PORT') ?: '6543';
            $this->db_name = getenv('DB_NAME') ?: 'postgres';
            $this->username = getenv('DB_USER') ?: 'postgres';
            $this->password = getenv('DB_PASSWORD') ?: '';
            $this->sslmode = getenv('DB_SSLMODE') ?: 'require';
        }

        if ($this->password === '') {
            die(
                'Veritabanı: Supabase veritabanı şifresi tanımlı değil. '
                . 'Proje kökünde .env oluşturup DB_PASSWORD=... veya DATABASE_URL=... ekleyin.'
            );
        }

        try {
            [$hostaddr, $dsnHost] = self::resolvePgsqlHostForDsn($this->host);
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
                $dsnHost,
                $this->port,
                $this->db_name,
                $this->sslmode
            );
            if ($hostaddr !== null) {
                $dsn .= ';hostaddr=' . $hostaddr;
            }

            $this->conn = new PDO($dsn, $this->username, $this->password);

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $useEmulatedPrepares = $this->port === '6543';
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, $useEmulatedPrepares);

        } catch (PDOException $exception) {
            die('Veritabanı bağlantı hatası oluştu: ' . $exception->getMessage());
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ?PDO {
        return $this->conn;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }
}
