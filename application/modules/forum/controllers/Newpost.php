<?php
/**
 * @copyright Ilch 2.0
 * @package ilch
 */

namespace Modules\Forum\Controllers;

use Modules\Forum\Mappers\Post as PostMapper;
use Modules\Forum\Mappers\Topic as TopicMapper;
use Modules\Forum\Mappers\Forum as ForumMapper;
use Modules\Forum\Models\ForumPost as ForumPostModel;
use Modules\Forum\Config\Config as ForumConfig;
use Ilch\Validation;

class Newpost extends \Ilch\Controller\Frontend
{
    public function indexAction()
    {
        $forumMapper = new ForumMapper();
        $topicMapper = new TopicMapper();

        $topicId = (int)$this->getRequest()->getParam('topicid');
        $forum = $forumMapper->getForumByTopicId($topicId);
        $cat = $forumMapper->getCatByParentId($forum->getParentId());
        $topic = $topicMapper->getPostById($topicId);
        $post = $topicMapper->getPostById($topicId);

        $this->getLayout()->getTitle()
                ->add($this->getTranslator()->trans('forum'))
                ->add($cat->getTitle())
                ->add($forum->getTitle())
                ->add($topic->getTopicTitle())
                ->add($this->getTranslator()->trans('newPost'));
        $this->getLayout()->getHmenu()
                ->add($this->getTranslator()->trans('forum'), ['controller' => 'index', 'action' => 'index'])
                ->add($cat->getTitle(), ['controller' => 'showcat','action' => 'index', 'id' => $cat->getId()])
                ->add($forum->getTitle(), ['controller' => 'showtopics', 'action' => 'index', 'forumid' => $forum->getId()])
                ->add($topic->getTopicTitle(), ['controller' => 'showposts', 'action' => 'index', 'topicid' => $topicId])
                ->add($this->getTranslator()->trans('newPost'), ['controller' => 'newpost','action' => 'index', 'topicid' => $topicId]);

        if ($this->getRequest()->getPost('saveNewPost')) {
            $postMapper = new PostMapper;
            $dateCreated = $postMapper->getDateOfLastPostByUserId($this->getUser()->getId());
            $isExcludedFromFloodProtection = is_in_array(array_keys($this->getUser()->getGroups()), explode(',', $this->getConfig()->get('forum_excludeFloodProtection')));

            if (!$isExcludedFromFloodProtection && ($dateCreated >= date('Y-m-d H:i:s', time()-$this->getConfig()->get('forum_floodInterval')))) {
                $this->addMessage('floodError', 'danger');
                $this->redirect()
                    ->withInput()
                    ->to(['controller' => 'newpost', 'action' => 'index', 'topicid' => $this->getRequest()->getParam('topicid')]);
            } else {
                $validation = Validation::create($this->getRequest()->getPost(), [
                    'text' => 'required'
                ]);

                if ($validation->isValid()) {
                    $dateTime = new \Ilch\Date();

                    $postModel = new ForumPostModel;
                    $postModel->setTopicId($topicId)
                        ->setUserId($this->getUser()->getId())
                        ->setText($this->getRequest()->getPost('text'))
                        ->setForumId($forum->getId())
                        ->setDateCreated($dateTime);
                    $this->trigger(ForumConfig::EVENT_SAVEPOST_BEFORE, ['model' => $postModel]);
                    $postMapper->save($postModel);
                    $this->trigger(ForumConfig::EVENT_SAVEPOST_AFTER, ['model' => $postModel]);
                    $this->trigger(ForumConfig::EVENT_ADDPOST_AFTER, ['postModel' => $postModel, 'forum' => $forum, 'category' => $cat, 'topic' => $topic, 'request' => $this->getRequest()]);

                    $postsPerPage = (empty($this->getConfig()->get('forum_postsPerPage'))) ? $this->getConfig()->get('defaultPaginationObjects') : $this->getConfig()->get('forum_postsPerPage');
                    $page = floor(($forumMapper->getCountPostsByTopicId($topicId) - 1) / $postsPerPage) + 1;

                    $this->redirect()
                        ->withMessage('saveSuccess')
                        ->to(['controller' => 'showposts', 'action' => 'index', 'topicid' => $topicId, 'page' => $page]);
                }
                $this->addMessage($validation->getErrorBag()->getErrorMessages(), 'danger', true);
                $this->redirect()
                    ->withInput()
                    ->withErrors($validation->getErrorBag())
                    ->to(['controller' => 'newpost', 'action' => 'index', 'topicid' => $this->getRequest()->getParam('topicid')]);
            }
        }

        $this->getView()->set('forumMapper', $forumMapper);
        $this->getView()->set('post', $post);
        $this->getView()->set('cat', $cat);
        $this->getView()->set('forum', $forum);
    }
}
