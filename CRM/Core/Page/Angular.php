<?php

/**
 * This page is simply a container; any Angular modules defined by CiviCRM (or by CiviCRM extensions)
 * will be activated on this page.
 *
 * @link https://issues.civicrm.org/jira/browse/CRM-14479
 */
class CRM_Core_Page_Angular extends CRM_Core_Page {
  /**
   * The weight to assign to any Angular JS module files
   */
  const DEFAULT_MODULE_WEIGHT = 200;

  /**
   * This function takes care of all the things common to all
   * pages. This typically involves assigning the appropriate
   * smarty variable :)
   *
   * @return string The content generated by running this page
   */
  function run() {
    $this->registerResources(CRM_Core_Resources::singleton());
    return parent::run();
  }

  /**
   * @param CRM_Core_Resources $res
   */
  public function registerResources(CRM_Core_Resources $res) {
    $modules = self::getAngularModules();

    $res->addSettingsFactory(function () use (&$modules) {
      // TODO optimization; client-side caching
      return array(
        'resourceUrls' => CRM_Extension_System::singleton()->getMapper()->getActiveModuleUrls(),
        'angular' => array(
          'modules' => array_merge(array('ngRoute'), array_keys($modules)),
        ),
      );
    });

    $res->addScriptFile('civicrm', 'packages/bower_components/angular/angular.min.js', 100, 'html-header', FALSE);
    $res->addScriptFile('civicrm', 'packages/bower_components/angular-route/angular-route.min.js', 110, 'html-header', FALSE);
    $headOffset = 0;
    foreach ($modules as $module) {
      if (!empty($module['css'])) {
        foreach ($module['css'] as $file) {
          $res->addStyleFile($module['ext'], $file, self::DEFAULT_MODULE_WEIGHT + (++$headOffset), 'html-header', TRUE);
        }
      }
      if (!empty($module['js'])) {
        foreach ($module['js'] as $file) {
          $res->addScriptFile($module['ext'], $file, self::DEFAULT_MODULE_WEIGHT  + (++$headOffset), 'html-header', TRUE);
        }
      }
    }
  }

  /**
   * Get a list of AngularJS modules which should be autoloaded
   *
   * @return array (string $name => array('ext' => string $key, 'js' => array $paths, 'css' => array $paths))
   */
  public static function getAngularModules() {
    $angularModules = array();
    $angularModules['ui.utils'] = array('ext' => 'civicrm', 'js' => array('packages/bower_components/angular-ui-utils/ui-utils.min.js'));
    $angularModules['ui.sortable'] = array('ext' => 'civicrm', 'js' => array('packages/bower_components/angular-ui-sortable/sortable.min.js'));
    $angularModules['unsavedChanges'] = array('ext' => 'civicrm', 'js' => array('packages/bower_components/angular-unsavedChanges/dist/unsavedChanges.min.js'));
    // https://github.com/jwstadler/angular-jquery-dialog-service
    $angularModules['angularFileUpload'] = array('ext' => 'civicrm', 'js' => array('packages/bower_components/angular-file-upload/angular-file-upload.min.js'));
    $angularModules['dialogService'] = array('ext' => 'civicrm' , 'js' => array('packages/bower_components/angular-jquery-dialog-service/dialog-service.js'));
    $angularModules['crmAttachment'] = array('ext' => 'civicrm', 'js' => array('js/angular-crmAttachment.js'), 'css' => array('css/angular-crmAttachment.css'));
    $angularModules['crmUi'] = array('ext' => 'civicrm', 'js' => array('js/angular-crm-ui.js', 'packages/ckeditor/ckeditor.js'));
    $angularModules['crmUtil'] = array('ext' => 'civicrm', 'js' => array('js/angular-crm-util.js'));
    $angularModules['ngSanitize'] = array('ext' => 'civicrm', 'js' => array('js/angular-sanitize.js'));

    foreach (CRM_Core_Component::getEnabledComponents() as $component) {
      $angularModules = array_merge($angularModules, $component->getAngularModules());
    }
    CRM_Utils_Hook::angularModules($angularModules);
    return $angularModules;
  }

}