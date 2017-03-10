<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Teams\Controllers\Admin;

use Modules\Teams\Mappers\Teams as TeamsMapper;
use Modules\Teams\Models\Teams as TeamsModel;
use Modules\User\Mappers\User as UserMapper;
use Modules\User\Mappers\Group as UserGroupMapper;
use Ilch\Validation;

class Index extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'manage',
                'active' => false,
                'icon' => 'fa fa-th-list',
                'url' => $this->getLayout()->getUrl(['controller' => 'index', 'action' => 'index']),
                [
                    'name' => 'add',
                    'active' => false,
                    'icon' => 'fa fa-plus-circle',
                    'url' => $this->getLayout()->getUrl(['controller' => 'index', 'action' => 'treat'])
                ]
            ],
            [
                'name' => 'applications',
                'active' => false,
                'icon' => 'fa fa-th-list',
                'url' => $this->getLayout()->getUrl(['controller' => 'applications', 'action' => 'index'])
            ],
            [
                'name' => 'settings',
                'active' => false,
                'icon' => 'fa fa-cogs',
                'url' => $this->getLayout()->getUrl(['controller' => 'settings', 'action' => 'index'])
            ]
        ];

        if ($this->getRequest()->getActionName() == 'treat') {
            $items[0][0]['active'] = true;
        } else {
            $items[0]['active'] = true;
        }

        $this->getLayout()->addMenu
        (
            'menuTeams',
            $items
        );
    }

    public function indexAction()
    {
        $teamsMapper = new TeamsMapper();

        $this->getLayout()->getAdminHmenu()
                ->add($this->getTranslator()->trans('menuTeams'), ['action' => 'index'])
                ->add($this->getTranslator()->trans('manage'), ['action' => 'index']);

        if ($this->getRequest()->getPost('check_teams')) {
            if ($this->getRequest()->getPost('action') == 'delete') {
                foreach ($this->getRequest()->getPost('check_teams') as $teamId) {
                    $teamsMapper->delete($teamId);
                }
            }
        }

        $this->getView()->set('teams', $teamsMapper->getTeams());
    }

    public function treatAction() 
    {
        $teamsMapper = new TeamsMapper();
        $userMapper = new UserMapper();
        $userGroupMapper = new UserGroupMapper();

        if ($this->getRequest()->getParam('id')) {
            $this->getLayout()->getAdminHmenu()
                    ->add($this->getTranslator()->trans('menuTeams'), ['action' => 'index'])
                    ->add($this->getTranslator()->trans('edit'), ['action' => 'treat']);

            $this->getView()->set('team', $teamsMapper->getTeamById($this->getRequest()->getParam('id')));
        } else {
            $this->getLayout()->getAdminHmenu()
                    ->add($this->getTranslator()->trans('menuTeams'), ['action' => 'index'])
                    ->add($this->getTranslator()->trans('add'), ['action' => 'treat']);
        }

        if ($this->getRequest()->isPost()) {
            $validation = Validation::create($this->getRequest()->getPost(), [
                'name' => 'required|unique:teams,name,'.$this->getRequest()->getParam('id'),
                'leader' => 'required|numeric|integer',
                'coLeader' => 'numeric|integer',
                'groupId' => 'required|numeric|integer|min:1',
                'optIn' => 'required|numeric|integer|min:0|max:1'
            ]);

            if ($this->getRequest()->getPost('leader') == $this->getRequest()->getPost('coLeader')) {
                $validation->getErrorBag()->addError('coLeader', $this->getTranslator()->trans('leaderCoLeaderIdentic'));
            }

            if ($validation->isValid()) {
                $model = new TeamsModel();

                if ($this->getRequest()->getParam('id')) {
                    $model->setId($this->getRequest()->getParam('id'));
                }

                if ($this->getRequest()->getPost('image_delete') != '') {
                    $teamsMapper->delImageById($this->getRequest()->getParam('id'));
                }

                if ($this->getRequest()->getPost('img') != '') {
                    $allowedFiletypes = $this->getConfig()->get('teams_filetypes');
                    $imageMaxHeight = $this->getConfig()->get('teams_height');
                    $imageMaxWidth = $this->getConfig()->get('teams_width');
                    $path = $this->getConfig()->get('teams_uploadpath');
                    $file = $_FILES['img']['name'];
                    $file_tmpe = $_FILES['img']['tmp_name'];
                    $endung = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $imageInfo = getimagesize($file_tmpe);

                    if (in_array($endung, explode(' ', $allowedFiletypes)) && strpos($imageInfo['mime'], 'image/') === 0) {
                        if ($this->getRequest()->getParam('id')) {
                            $teamsMapper->delImageById($this->getRequest()->getParam('id'));
                        }

                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                        $newName = str_replace(' ','',$this->getRequest()->getPost('name'));
                        $image = $path.$newName.'.'.$endung;

                        if (move_uploaded_file($file_tmpe, $image)) {
                            if ($width > $imageMaxWidth OR $height > $imageMaxHeight) {
                                $upload = new \Ilch\Upload();

                                // Take an educated guess on how big the image is going to be in memory to decide if it should be tried to crop the image.
                                if (($upload->returnBytes(ini_get('memory_limit')) - memory_get_usage(true)) < $upload->guessRequiredMemory($image)) {
                                    unlink($image);
                                    $this->addMessage('failedFilesize', 'warning');
                                } else {
                                    $thumb = new \Thumb\Thumbnail();
                                    $thumb -> Thumbsize = ($imageMaxWidth <= $imageMaxHeight) ? $imageMaxWidth : $imageMaxHeight;
                                    $thumb -> Square = true;
                                    $thumb -> Thumblocation = $path;
                                    $thumb -> Cropimage = [3,1,50,50,50,50];
                                    $thumb -> Createthumb($image, 'file');
                                }
                            }

                            $model->setImg($image);
                        }
                    } else {
                        $this->addMessage('failedFiletypes', 'warning');
                    }
                }

                $model->setName($this->getRequest()->getPost('name'))
                    ->setLeader($this->getRequest()->getPost('leader'))
                    ->setCoLeader($this->getRequest()->getPost('coLeader'))
                    ->setGroupId($this->getRequest()->getPost('groupId'));
                $model->setOptIn($this->getRequest()->getPost('optIn'));
                $teamsMapper->save($model);

                $this->redirect()
                    ->withMessage('saveSuccess')
                    ->to(['action' => 'index']);
            }
            $this->addMessage($validation->getErrorBag()->getErrorMessages(), 'danger', true);
            $this->redirect()
                ->withInput()
                ->withErrors($validation->getErrorBag())
                ->to(['action' => 'treat']);
        }

        $this->getView()->set('userList', $userMapper->getUserList());
        $this->getView()->set('userGroupList', $userGroupMapper->getGroupList());
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $teamsMapper = new TeamsMapper();
            $teamsMapper->delete($this->getRequest()->getParam('id'));

            $this->addMessage('deleteSuccess');
        }

        $this->redirect(['action' => 'index']);
    }
}
