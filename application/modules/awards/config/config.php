<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Awards\Config;

class Config extends \Ilch\Config\Install
{
    public $config = [
        'key' => 'awards',
        'version' => '1.2',
        'icon_small' => 'fa-trophy',
        'author' => 'Veldscholten, Kevin',
        'link' => 'http://ilch.de',
        'languages' => [
            'de_DE' => [
                'name' => 'Auszeichnungen',
                'description' => 'Hier können Auszeichnungen an Benutzer oder Teams verliehen werden.',
            ],
            'en_EN' => [
                'name' => 'Awards',
                'description' => 'Here you can award users or teams an award.',
            ],
        ],
        'ilchCore' => '2.0.0',
        'phpVersion' => '5.6'
    ];

    public function install()
    {
        $this->db()->queryMulti($this->getInstallSql());
    }

    public function uninstall()
    {
        $this->db()->queryMulti('DROP TABLE `[prefix]_awards`');
    }

    public function getInstallSql()
    {
        return 'CREATE TABLE IF NOT EXISTS `[prefix]_awards` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `date` DATE NOT NULL,
                  `rank` INT(11) NOT NULL,
                  `image` VARCHAR(255) NOT NULL,
                  `event` VARCHAR(100) NOT NULL,
                  `url` VARCHAR(150) NOT NULL,
                  `ut_id` VARCHAR(255) NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
    }

    public function getUpdate($installedVersion)
    {
        switch ($installedVersion) {
            case "1.1":
                $this->db()->query('ALTER TABLE `[prefix]_awards` ADD `image` VARCHAR(255) NOT NULL AFTER `rank`;');
                $this->db()->query('ALTER TABLE `[prefix]_awards` MODIFY `ut_id` VARCHAR(255) NOT NULL;');

                $awards = $this->db()->select('*')
                    ->from('awards')
                    ->execute()
                    ->fetchRows();

                foreach ($awards as $key => $value) {
                    if ($value['typ'] == 2) {
                        $this->db()->query('UPDATE `[prefix]_awards` SET `ut_id` = "2_'.$value['ut_id'].'" WHERE `typ` = 2;');
                    } else {
                        $this->db()->query('UPDATE `[prefix]_awards` SET `ut_id` = "1_'.$value['ut_id'].'" WHERE `typ` = 1;');
                    }
                };

                $this->db()->query('ALTER TABLE `[prefix]_awards` DROP `typ`;');
        }
    }
}
