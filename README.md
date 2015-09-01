# Codeception-DBMultiple
If your project is involving multiple-db that run at one time, and you want write a test for it, this module can be as a solution to load multiple *.sql file as a fixture for codeception test.

DbMultiple working very simple. it can read all files as a dump (that we already define the path and the extensions of its file) then loaded it into test-database.

DbMultiple is extended class from Db-Codeception's Module, so it has same config with Db-Codeceptions's Module, but with some additional config: 
`dumpPath` and `dumpFileExtension`.

# Example Configuration
    class_name: FunctionalTester
        modules:
            enabled:
                - \Helper\Functional
                - PhpBrowser
                - DBMultiple:
                    dumpPath: "tests/_data"
                    dumpFileExtension: "sql"
                    dsn: 'mysql:host=127.0.0.1;dbname=test-project'
                    user: 'root'
                    password: 'root'
                    reconnect: true,
                    cleanup: true
            config:
                    url: 'http://localhost/test.php'
                PhpBrowser:
                    url: 'http://localhost/'
