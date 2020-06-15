<?php declare(strict_types=1);
namespace LeMaX10\MultiSite\Classes\Behaviors;

use LeMaX10\MultiSite\Classes\SiteManager;
use October\Rain\Extension\ExtensionBase;
use Backend\Classes\Controller;

/**
 * Class ControllerBehavior
 * @package LeMaX10\MultiSite\Classes\Behaviors
 */
class ControllerBehavior extends ExtensionBase
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * ControllerBehavior constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     *
     */
    public function onMultisiteLoadModal()
    {

        return $this->controller->makePartial(
            __DIR__ . '/views/changesite.modal',
            [
                'sites'  => SiteManager::instance()->getAll(),
                'isMain' => SiteManager::instance()->mainDomain()
            ]
        );
    }

    public function onMultisiteChangeSite()
    {

    }
}
