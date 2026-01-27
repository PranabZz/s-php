<?php

require_once __DIR__ . '/vendor/autoload.php';

use Sphp\Core\Database;

class Command
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/app/config/config.php';
        // Override database path for local execution
        $this->config['database'] = __DIR__ . '/.data/database.sqlite';
        try {
            $this->db = new Database($this->config);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage() . "\n");
        }
    }

    public function execute($command)
    {
        switch (strtolower($command)) {
            case 'dbview':
            case 'db':
                $this->dbviewCommand();
                break;
            case 'help':
                $this->helpCommand();
                break;
            default:
                echo "❌ Unknown command: '$command'. Type 'php do help' to see available commands.\n";
                exit(1);
        }
    }

    private function dbviewCommand()
    {
        echo "Connected to database.\n";
        echo "Enter your SQL queries. Type 'exit' or 'quit' to close the interface.\n";

        while (true) {
            echo "phpdb> ";
            $line = trim(fgets(STDIN));

            if (strtolower($line) === 'exit' || strtolower($line) === 'quit') {
                echo "Exiting phpdb.\n";
                break;
            }

            if (empty($line)) {
                continue;
            }

            try {
                // Execute the query
                $results = $this->db->query($line);

                if (is_array($results) && count($results) > 0) {
                    // Display results in a tabular format
                    $headers = array_keys($results[0]);

                    // Calculate column widths
                    $columnWidths = [];
                    foreach ($headers as $header) {
                        $columnWidths[$header] = strlen($header);
                    }
                    foreach ($results as $row) {
                        foreach ($row as $key => $value) {
                            $columnWidths[$key] = max($columnWidths[$key], strlen((string)$value));
                        }
                    }

                    // Print header
                    foreach ($headers as $header) {
                        echo str_pad($header, $columnWidths[$header] + 2);
                    }
                    echo "\n";
                    foreach ($headers as $header) {
                        echo str_pad(str_repeat('-', $columnWidths[$header]), $columnWidths[$header] + 2);
                    }
                    echo "\n";

                    // Print rows
                    foreach ($results as $row) {
                        foreach ($headers as $header) {
                            echo str_pad((string)$row[$header], $columnWidths[$header] + 2);
                        }
                        echo "\n";
                    }
                } elseif (is_array($results) && count($results) === 0) {
                    echo "Query executed successfully, no results to display.\n";
                } else {
                    // For non-SELECT queries (INSERT, UPDATE, DELETE), PDO query might return empty array or boolean true
                    echo "Query executed successfully.\n";
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    private function helpCommand()
    {
        echo "Available commands:\n";
        echo "  dbview, db  -  Opens an interactive database shell.\n";
        echo "  help        -  Displays this help message.\n";
    }
}