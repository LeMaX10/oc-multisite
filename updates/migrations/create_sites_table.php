<?php namespace LeMaX10\MultiSite\Updates\Migrations;

use DB;
use PDO;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class create_sites_table
 * @package LeMaX10\MultiSite\Updates\Migrations
 */
class create_sites_table extends Migration
{
    /**
     * @var string
     */
    const TABLE = 'lemax10_multisite_sites';

    /**
     *
     */
    public function up()
    {
        Schema::create(static::TABLE, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('slug')->unique();

            $table->char('domain', 255)->unique();

            $table->json('alt_domains')->nullable();
            $table->json('config')->nullable();

            $table->char('theme', 50);

            $table->boolean('is_active')->default(1);
            $table->boolean('is_protected')->default(0);
            $table->boolean('is_https')->default(0);

            $table->timestamps();
        });
    }

    /**
     *
     */
    public function down()
    {
        Schema::dropIfExists(static::TABLE);
    }
}
