<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Classes\Support;

use Illuminate\Support\Facades\DB;
use PDO;

class DatabaseSupport
{
    /**
     * @return bool
     */
    public function supportJson(): bool
    {
        $driver = DB::connection()
            ->getPdo()
            ->getAttribute(PDO::ATTR_DRIVER_NAME);

        switch ($driver) {
            case 'mysql':
                return $this->supportedMysql();
                break;
            case 'pgsql':
                return $this->supportedPgsql();
                break;
            default:
                return false;
        }
    }

    /**
     * @return bool
     */
    protected function supportedMysql(): bool
    {
        $requiredVersion = '5.7.8';
        $myVersion = DB::connection()
            ->getPdo()
            ->getAttribute(PDO::ATTR_SERVER_VERSION);

        if (strpos($myVersion, 'MariaDB') !== false) {
            $requiredVersion = '10.2.7-MariaDB';
            $myVersion = DB::select('SELECT VERSION() as version')[0]->version;
        }

        return version_compare($myVersion, $requiredVersion, 'ge');
    }

    /**
     * @return bool
     */
    protected function supportedPgsql(): bool
    {
        $requiredVersion = '9.2';
        $myVersion = DB::select('SELECT VERSION() as version')[0]->version;

        return version_compare($myVersion, $requiredVersion, 'ge');
    }
}
