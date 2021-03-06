<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Birthday\Controllers;

use Modules\Birthday\Mappers\Birthday as BirthdayMapper;
use Modules\User\Mappers\User as UserMapper;

class Index extends \Ilch\Controller\Frontend
{
    public function indexAction()
    {
        $userMapper = new UserMapper();
        $birthdayMapper = new BirthdayMapper();

        $this->getLayout()->getHmenu()
                ->add($this->getTranslator()->trans('menuBirthdayList'), ['controller' => 'index']);

        $this->getView()->set('birthdayListNOW', $birthdayMapper->getBirthdayUserList());
        $this->getView()->set('birthdayList', $userMapper->getUserList());
    }
}


