<?php namespace LeMaX10\MultiSite\Controllers;

use Url;
use Request;
use Redirect;
use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use ApplicationException;

/**
 * Sites Back-end Controller
 */
class Sites extends Controller
{
    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.FormController'
    ];

    public $requiredPermissions = ['lemax.multisite.access'];


    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('LeMaX10.MultiSite', 'system', 'sites');
        SettingsManager::setContext('LeMaX10.MultiSite', 'sites');
    }
}
