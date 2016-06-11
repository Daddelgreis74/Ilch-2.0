<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Jobs\Config;

class Config extends \Ilch\Config\Install
{
    public $config = [
        'key' => 'jobs',
        'author' => 'Veldscholten, Kevin',
        'icon_small' => 'fa-briefcase',
        'languages' => [
            'de_DE' => [
                'name' => 'Jobs',
                'description' => 'Hier können Jobs erstellt werden.',
            ],
            'en_EN' => [
                'name' => 'Jobs',
                'description' => 'Here you can create jobs.',
            ],
        ]
    ];

    public function install()
    {
        $this->db()->queryMulti($this->getInstallSql());
    }

    public function uninstall()
    {
        $this->db()->queryMulti('DROP TABLE `[prefix]_jobs`');
    }

    public function getInstallSql()
    {
        return 'CREATE TABLE IF NOT EXISTS `[prefix]_jobs` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `title` VARCHAR(150) NOT NULL,
                  `text` MEDIUMTEXT NOT NULL,
                  `email` VARCHAR(100) NOT NULL,
                  `show` INT(11) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
    }

    public function getUpdate()
    {

    }
}
