<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    /**
     * The purpose of this class is to drill through Modules,
     * build the database for freezing, and provide the other
     * function required to complete an install.
     */
    class InstallUtil
    {
        public static function getSupportedDatabaseTypes()
        {
            return array('mysql');
        }

        ///////////////////////////////////////////////////////////////////////
        // Methods that only check things. They don't change anything.
        // The aim is that when everything that can be checked passes
        // its checks, the subsequent methods that modify things should
        // be expected to succeed.

        /**
         * @param $requiredVersions An array mapping server names to minimum
         *                          required versions. eg: array('apache' => '2.2.16')
         */
        public static function checkWebServer(array $minimumRequiredVersions, /* out */ &$actualVersion)
        {
            $matches = array();
            if (preg_match('/([^\/]+)\/(\d+\.\d+(.\d+))?/', $_SERVER['SERVER_SOFTWARE'], $matches)) // Not Coding Standard
            {
                $serverName    = strtolower($matches[1]);
                $actualVersion =            $matches[2];
                if (array_key_exists($serverName, $minimumRequiredVersions))
                {
                    return self::checkVersion($minimumRequiredVersions[$serverName], $actualVersion);
                }
            }
            return false;
        }

        /**
         * @returns The Apache ModDeflate version, or false if not installed.
         */
        public static function checkApacheModDeflate()
        {
        }

        /**
         * @param $minimumRequiredVersion Minimum required php version in "5.3.3" format.
         */
        public static function checkPhp($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = PHP_VERSION;
            return self::checkVersion($minimumRequiredVersion, $actualVersion);
        }

        /**
         * @returns true/false for if the timezone has been set.
         */
        public static function checkPhpTimezoneSetting()
        {
            $timezone = ini_get('date.timezone');
            return !empty($timezone);
        }

        /**
         * @returns true, or the max memory setting is less than the minimum required.
         */
        public static function checkPhpMaxMemorySetting($minimumMemoryRequireBytes, /* out */ & $actualMemoryLimitBytes)
        {
            $memoryLimit            = ini_get('memory_limit');
            $actualMemoryLimitBytes = self::getBytes($memoryLimit);
            return $minimumMemoryRequireBytes <= $actualMemoryLimitBytes;
        }

        /**
         * @returns true, or the max memory setting is less than the minimum required.
         */
        public static function checkPhpUploadSizeSetting($minimumUploadRequireBytes, /* out */ & $actualUploadLimitBytes)
        {
            $memoryLimit            = ini_get('upload_max_filesize');
            $actualUploadLimitBytes = self::getBytes($memoryLimit);
            return $minimumUploadRequireBytes <= $actualUploadLimitBytes;
        }

        protected static function getBytes($size)
        {
            if (preg_match('/\d+[G|M|K]/i', $size)) // Not Coding Standard
            {
                switch (strtoupper(substr(trim($size), -1)))
                {
                    case 'G':
                        return (int)$size * 1024 * 1024 * 1024;

                    case 'M':
                        return (int)$size * 1024 * 1024;

                    case 'K':
                        return (int)$size * 1024;

                    default:
                        return (int)$size;
                }
            }
            else
            {
                return 0;
            }
        }

        /**
         * @returns true, or the MySQL version if less than required, or false if not installed.
         */
        public static function checkDatabase($databaseType, $minimumRequiredVersion, /* out */ &$actualVersion)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            switch ($databaseType)
            {
                case 'mysql':
                        $PhpDriverVersion = phpversion('mysql');
                        if($PhpDriverVersion !== null)
                        {
                            $actualVersion = mysql_get_server_info();
                            if($actualVersion !== null)
                            {
                                return self::checkVersion($minimumRequiredVersion, $actualVersion);
                            }
                        }
                        return false;
                default:
                    throw new NotSupportedException();
            }
        }

        /**
         * @returns true, or the APC version if less than required, or false if not installed.
         */
        public static function checkAPC($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = phpversion('apc');
            if ($actualVersion !== false && $actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        /**
         * @returns true, or the Soap version if less than required, or false if not installed.
         */
        public static function checkSoap()
        {
            return in_array('soap', get_loaded_extensions());
        }

        /**
         * @returns true, or the memcache version if less than required, or false if not installed.
         */
        public static function checkMemcache($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = phpversion('memcache');
            if ($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        /**
         * @returns true, or the Tidy version if less than required, or false if not installed.
         */
        public static function checkTidy($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = phpversion('tidy');
            if ($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        /**
         * @returns true, or the Curl version if less than required, or false if not installed.
         */
        public static function checkCurl($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            if (!in_array('curl', get_loaded_extensions()))
            {
                return false;
            }
            $versionInfo   = curl_version();
            $actualVersion = $versionInfo['version'];
            if($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        public static function checkYii($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = Yii::getVersion();
            if($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        public static function checkRedBean($minimumRequiredVersion, /* out */ &$actualVersion)
        {
            $actualVersion = R::getVersion();
            if($actualVersion !== null)
            {
                return self::checkVersion($minimumRequiredVersion, $actualVersion);
            }
            return false;
        }

        public static function checkRedBeanPatched()
        {
            $contents = file_get_contents('../redbean/rb.php');
            return preg_match('/public function __call\(\$method, \$args\) {\n\s+return null;/', $contents) != 0; // Not Coding Standard
        }

        /**
         * @returns true, or an array of connection error number and string .
         */
        public static function checkMemcacheConnection($host, $port)
        {
            assert('is_string($host) && $host != ""');
            assert('is_int   ($port) && $port >= 1024');
            $errorNumber = 0;
            $errorString = '';
            $timeout     = 2;
            $connection = @fsockopen($host, $port, $errorNumber, $errorString, $timeout);
            if ($connection !== false)
            {
                fclose($connection);
                return true;
            }
            return array($errorNumber, $errorString);
        }

        /**
         * @returns true, or an error string .
         */
        public static function checkDatabaseConnection($databaseType, $host, $rootUsername, $rootPassword)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host, $rootUsername, $rootPassword)) === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;

                default:
                    throw new NotSupportedException();
            }
        }

        /**
         * @returns true/false for if the named database exists.
         */
        public static function checkDatabaseExists($databaseType, $host, $rootUsername, $rootPassword,
                                                   $databaseName)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host, $rootUsername, $rootPassword)) === false ||
                                       @mysql_select_db($databaseName, $connection)         === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;

                default:
                    throw new NotSupportedException();
            }
        }

        /**
         * @returns true/false for if the named database user exists.
         */
        public static function checkDatabaseUserExists($databaseType, $host, $rootUsername, $rootPassword, $username)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_string($username)     && $username     != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result             = true;
                    $query              = "select count(*) from user where Host in ('%', '$host') and User ='$username'";
                    $connection         = @mysql_connect($host, $rootUsername, $rootPassword);
                    $databaseConnection = @mysql_select_db('mysql', $connection);
                    $queryResult        = @mysql_query($query, $connection);
                    $row                = @mysql_fetch_row($queryResult);
                    if ($connection === false || $databaseConnection === false || $queryResult === false ||
                        $row === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    else
                    {
                        if($row == null)
                        {
                            $result = array(mysql_errno(), mysql_error());
                        }
                        else
                        {
                            assert('is_array($row) && count($row) == 1 && $row[0] >= 1');
                            $result = $row[0] == 1;
                        }
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;

                default:
                    throw new NotSupportedException();
            }
        }

        ///////////////////////////////////////////////////////////////////////
        // Methods that modify things.
        // The aim is that when all of the checks above pass
        // these should be expected to succeed.

        /**
         * Creates the named database, dropping it first if it already exists.
         */
        public static function createDatabase($databaseType, $host, $rootUsername, $rootPassword, $databaseName)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host, $rootUsername, $rootPassword))                   === false ||
                                       @mysql_query("drop   database if exists `$databaseName`", $connection) === false ||
                                       @mysql_query("create database           `$databaseName`", $connection) === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;

                default:
                    throw new NotSupportedException();
            }
        }

        /**
         * Creates the named database user, dropping it first if it already exists.
         * Grants the user full access on the given database.
         */
        public static function createDatabaseUser($databaseType, $host, $rootUsername, $rootPassword,
                                                  $databaseName, $username, $password)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host         != ""');
            assert('is_string($rootUsername) && $rootUsername != ""');
            assert('is_string($rootPassword) && $rootPassword != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            switch ($databaseType)
            {
                case 'mysql':
                    $result = true;
                    if (($connection = @mysql_connect($host, $rootUsername, $rootPassword))                               === false ||
                                       // The === 666 is to execute this command ignoring whether it fails.
                                       @mysql_query("drop user `$username`", $connection) === 666                                  ||
                                       @mysql_query("grant all on `$databaseName`.* to `$username`",        $connection) === false ||
                                       @mysql_query("set password for `$username` = password('$password')", $connection) === false)
                    {
                        $result = array(mysql_errno(), mysql_error());
                    }
                    if (is_resource($connection))
                    {
                        mysql_close($connection);
                    }
                    return $result;

                default:
                    throw new NotSupportedException();
            }
        }

        /**
         * Connects to the database.
         */
        public static function connectToDatabase($databaseType, $host, $databaseName, $username, $password)
        {
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($host)         && $host         != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            $connectionString = "$databaseType:host=$host;dbname=$databaseName"; // Not Coding Standard
            self::connectToDatabaseWithConnectionString($connectionString, $username, $password);
        }

        /**
         * Connects to the database with a connection string.
         */
        public static function connectToDatabaseWithConnectionString($connectionString, $username, $password)
        {
            assert('is_string($connectionString) && $connectionString != ""');
            assert('is_string($username)         && $username         != ""');
            assert('is_string($password)');
            RedBeanDatabase::setup($connectionString, $username, $password);
            assert('RedBeanDatabase::isSetup()');
        }

        /**
         * Creates the first user.
         */
        public static function createSuperUser($username, $password)
        {
            $user = new User();
            $user->username     = $username;
            $user->title->value = 'Mr';
            $user->firstName    = 'Super';
            $user->lastName     = 'User';
            $user->setPassword($password);
            $saved = $user->save();
            assert('$saved'); // TODO - handle this properly.

            $group = Group::getByName('Super Administrators');
            $group->users->add($user);
            $saved = $group->save();
            assert('$saved'); // TODO - handle this properly.
            return $user;
        }

        /**
         * Drops all the tables in the databaes.
         */
        public static function dropAllTables()
        {
            $tableNames = R::getCol('show tables');
            foreach ($tableNames as $tableName)
            {
                R::exec("drop table $tableName");
            }
            assert('count(R::getCol("show tables")) == 0');
        }

        /**
         * Auto builds the database.
         */
        public static function autoBuildDatabase(& $messageLogger)
        {
            $rootModels = array();
            foreach (Module::getModuleObjects() as $module)
            {
                $moduleAndDependenciesRootModelNames = $module->getRootModelNamesIncludingDependencies();
                $rootModels = array_merge($rootModels, array_diff($moduleAndDependenciesRootModelNames, $rootModels));
            }
            RedBeanDatabaseBuilderUtil::autoBuildModels($rootModels, $messageLogger);
        }

        /**
         * Freezes the database.
         */
        public static function freezeDatabase()
        {
            RedBeanDatabase::freeze();
        }

        /**
         * Closes the database.
         */
        public static function close()
        {
            RedBeanDatabase::close();
        }

        /**
         * Writes configuration to debug.php and phpInstance.php.
         */
        public static function writeConfiguration($instanceRoot,
                                                  $databaseType, $databaseHost, $databaseName, $username, $password,
                                                  $memcacheHost = null, $memcachePort = null,
                                                  $language)
        {
            assert('is_dir($instanceRoot)');
            assert('in_array($databaseType, self::getSupportedDatabaseTypes())');
            assert('is_string($databaseHost) && $databaseHost != ""');
            assert('is_string($databaseName) && $databaseName != ""');
            assert('is_string($username)     && $username     != ""');
            assert('is_string($password)');
            assert('is_string($memcacheHost) || $memcacheHost == null');
            assert('(is_int   ($memcachePort) && $memcachePort >= 1024) || $memcachePort == null');
            assert('is_string($language)     && $language     != ""');

            $debugConfigFile       = "$instanceRoot/protected/config/debug.php";
            $perInstanceConfigFile = "$instanceRoot/protected/config/perInstance.php";

            // NOTE: These keep the tidy formatting of the files they are modifying - the whitespace matters!

            $contents = file_get_contents($debugConfigFile);
            $contents = preg_replace('/\$debugOn\s*=\s*true;/',
                                     '$debugOn = false;',
                                     $contents);
            file_put_contents($debugConfigFile, $contents);

            $contents = file_get_contents($perInstanceConfigFile);
            $contents = preg_replace('/\$language\s*=\s*\'[a-z]+\';/', // Not Coding Standard
                                     "\$language         = '$language';",
                                     $contents);
            $contents = preg_replace('/\$connectionString\s*=\s*\'[a-z]+:host=[^;]+;dbname=[^;]+;/', // Not Coding Standard
                                   "\$connectionString = '$databaseType:host=$databaseHost;dbname=$databaseName';", // Not Coding Standard
                                     $contents);
            $contents = preg_replace('/\$username\s*=\s*\'[^\']+\';/', // Not Coding Standard
                                     "\$username         = '$username';",
                                     $contents);
            $contents = preg_replace('/\$password\s*=\s*\'[^\']+\';/', // Not Coding Standard
                                     "\$password         = '$password';",
                                     $contents);
            $contents = preg_replace('/\$memcacheServers\s*=\s*array\(.*?array\(\s+\'host\'\s*=>\s*\'[^\']+\',\s*\'port\'\s*=>\s*\d{4,},/s', // Not Coding Standard
                      "\$memcacheServers  = array( // An empty array means memcache is not used.
                            array(
                                'host'   => '$memcacheHost',
                                'port'   => $memcachePort,",
                                     $contents);
            $contents = preg_replace('/\s+\/\/ REMOVE THE REMAINDER OF THIS FILE FOR PRODUCTION.*?>/s', // Not Coding Standard
                                     "\n>?",
                                     $contents);
            file_put_contents($perInstanceConfigFile, $contents);
        }

        public static function isDebugConfigWritable($instanceRoot)
        {
            $debugConfigFile = "$instanceRoot/protected/config/debug.php";
            return is_writable($debugConfigFile);
        }

        public static function isPerInstanceConfigWritable($instanceRoot)
        {
            $perInstanceConfigFile = "$instanceRoot/protected/config/perInstance.php";
            return is_writable($perInstanceConfigFile);
        }

        /**
         * Writes into perInstance.php that the installation is complete.
         */
        public static function writeInstallComplete($instanceRoot)
        {
            assert('is_dir($instanceRoot)');
            $perInstanceConfigFile = "$instanceRoot/protected/config/perInstance.php";
            // NOTE: These keep the tidy formatting of the files they are modifying - the whitespace matters!
            $contents = file_get_contents($perInstanceConfigFile);
            $contents = preg_replace('/\$installed\s*=\s*false;/',
                                     '$installed = true;',
                                     $contents);
            file_put_contents($perInstanceConfigFile, $contents);
        }

        public static function isVersion($version)
        {
            return preg_match('/^\d+\.\d+(.\d+)?/', $version) == 1; // Not Coding Standard
        }

        protected static function checkVersion($minimumRequiredVersion, $actualVersion)
        {
            assert('self::isVersion($minimumRequiredVersion)');
            assert('self::isVersion($actualVersion)');
            if (preg_match('/^\d+\.\d+$/', $actualVersion) == 1) // Not Coding Standard
            {
                $actualVersion .= '.0';
            }
            return version_compare($actualVersion, $minimumRequiredVersion) >= 0;
        }

        protected static function getVersionFromPhpInfo($regEx)
        {
            ob_start();
            phpinfo();
            $phpInfo = trim(ob_get_clean());
            $matches = array();
            if (preg_match("/$regEx/si", $phpInfo, $matches) == 1)
            {
                return $matches[1];
            }
            return false;
        }
    }
?>
