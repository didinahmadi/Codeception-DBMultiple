# Codeception-DBMultiple
Codeception's module to load multiple sql-dump

# Example Configuration
    class_name: FunctionalTester
    modules:
        enabled:
            # add framework module here
            - Yii1
            - \Helper\Functional
            - PhpBrowser
            - Db
            - DBMultiple:
                dumpPath: "tests/_data"
                dumpFileExtension: "sql"
                connection: 'Db'
        config:
            Yii1:
                appPath: '/project/trunk/test.php'
                url: 'https://localhost/test.php'
            PhpBrowser:
                url: 'https://test.localhost/'
            Db:
                dsn: 'mysql:host=127.0.0.1;dbname=test-database'
                user: 'root'
                password: 'root'
                dump: ''