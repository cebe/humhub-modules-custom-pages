<?php

namespace humhub\modules\custom_pages\controllers;

use Yii;
use yii\web\HttpException;
use humhub\components\Controller;
use humhub\modules\custom_pages\models\Page;
use humhub\modules\custom_pages\components\Container;
use humhub\modules\custom_pages\components\TemplateViewBehavior;

/**
 * Controller for viewing Pages.
 *
 * @author buddha
 */
class ViewController extends Controller
{
    
    /**
     * @inhritdoc
     */
    public function behaviors()
    {
        return [
            ['class' => TemplateViewBehavior::className()],
        ];
    }
    
      /**
     * Is used to view/render a Page of a certain page content type.
     * 
     * This action expects an page id as request parameter.
     * 
     * @return type
     * @throws HttpException if the page was not found
     */
    public function actionIndex()
    {
        $page = Page::findOne(['id' => Yii::$app->request->get('id')]);

        if ($page === null) {
            throw new HttpException('404', 'Could not find requested page');
        }

        if ($page->admin_only == 1 && !Yii::$app->user->isAdmin()) {
            throw new HttpException(403, 'Access denied!');
        }

        if ($page->navigation_class == Page::NAV_CLASS_ACCOUNTNAV) {
            $this->subLayout = "@humhub/modules/user/views/account/_layout";
        }

        if ($page->type == Container::TYPE_HTML) {
            return $this->render('html', array('page' => $page, 'html' => $page->content, 'title' => $page->title));
        } elseif ($page->type == Container::TYPE_IFRAME) {
            return $this->render('iframe', array('page' => $page, 'url' => $page->content, 'navigationClass' => $page->navigation_class));
        } elseif ($page->type == Container::TYPE_LINK) {
            return $this->redirect($page->content);
        } elseif ($page->type == Container::TYPE_TEMPLATE) {
            return $this->viewTemplatePage($page);
        } elseif ($page->type == Container::TYPE_MARKDOWN) {
            return $this->render('markdown', array(
                'page' => $page,
                'md' => $page->content,
                'navigationClass' => $page->navigation_class,
                'title' => $page->title
            ));
        } else {
            throw new HttpException('500', 'Invalid page type!');
        }
    }
    
    /**
     * This redirect is needed within some common views shared with container page logic.
     * @return type
     */
    public function actionView()
    {
        return $this->actionIndex();
    }
    
    /**
     * This redirect is needed within some common views shared with container page logic.
     * @return type
     */
    public function actionEdit($id)
    {
        return $this->redirect(\yii\helpers\Url::to(['/custom_pages/admin/edit', 'id' => $id]));
    }
}
