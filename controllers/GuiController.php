<?php

namespace mdm\admin\controllers;


use mdm\admin\models\searchs\AuthItem;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\rbac\Item;
use yii\web\Controller;

/**
 * GuiController graph controller
 */
class GuiController extends Controller
{
    /**
     * Display RBAC graph
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Get all RBAC items for graph
     * @return string json data for Nodes and Links
     */
    public function actionRbacItems()
    {
        $searchModel = new AuthItem(['type' => Item::TYPE_ROLE]);
        $items = $searchModel->search([])->allModels;
        $searchModel = new AuthItem(['type' => Item::TYPE_PERMISSION]);
        $items = array_merge($searchModel->search([])->allModels, $items);
        $nodes = array_map(function ($item) {
            /** @var Item $item */
            return [
                'id' => $item->name,
                'type' => $item->type,
                'rule' => $item->ruleName,
            ];
        }, $items);
        $keys = array_keys($items);

        /* @var \yii\rbac\ManagerInterface $authManager */
        $authManager = \Yii::$app->getAuthManager();

        foreach($items as $item) {
            foreach ($authManager->getChildren($item->name) as $child) {
                $source = array_search($child->name, $keys);
                $target = array_search($item->name, $keys);
                if ($source !== null && $target !== null) {
                    $links[] = [
                        'source' => $child->name,
                        'target' => $item->name,
                    ];
                }
            }
        }

        return Json::encode([
            'nodes' => array_values($nodes),
            'links' => $links,
        ]);
    }
}