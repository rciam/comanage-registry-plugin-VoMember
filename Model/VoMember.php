<?php

class VoMember extends AppModel {
  public $name = "VoMember";

  // Required by COmanage Plugins
  public $cmPluginType = 'other';

  public $actsAs = array('Containable');

  // Validation rules for table elements
  public $validate = array(
    'co_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    ),
    'base_url' => array(
      'rule' => array('maxLength', 512),
      'required' => true,
      'allowEmpty' => false,
      'message' => array('Base URL must not exceed 50 characters.'),
    ),
    'endpoint_ops' => array(
      'rule' => array('maxLength', 512),
      'required' => true,
      'allowEmpty' => false,
      'message' => 'Service endpoint must be provided',
    ),
    'authkey_ops' => array(
      'rule' => array('maxLength', 512),
      'required' => true,
      'allowEmpty' => false,
      'message' => 'The authentication key must be provided',
    ),
  );

  /**
   * Actions to take before a save operation is executed.
   *
   * @since  COmanage Registry v3.1.0
   */

  public function beforeSave($options = array())
  {
    if (isset($this->data["VoMember"]["authkey_ops"])) {
      $key = Configure::read('Security.salt');
      Configure::write('Security.useOpenSsl', true);
      $authkey = base64_encode(Security::encrypt($this->data["VoMember"]["authkey_ops"], $key));
      $this->data["VoMember"]["authkey_ops"] = $authkey;
    }
  }

  /**
   * Expose menu items.
   *
   * @ return Array with menu location type as key and array of labels, controllers, actions as values.
   */

  public function cmPluginMenus()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $menu_items = array();
    $menu_items['cogroups'][_txt('ct.vo_members.pl')] = array(
      'controller' => "voms_members",
      'action' => 'index',
    );
    $menu_items['coconfig'][_txt('ct.vo_members.pl')] = array(
      'controller' => "vo_members",
      'action' => 'edit',
      'icon' => 'account_box' // fixme: icon does not work
    );
    return $menu_items;
  }

  /**
   * Expose sidebar menu items.
   *
   * @ return Array with menu location type as key and array of labels, controllers, actions as values.
   */

  public function cmPluginSidebar()
  {
    $action_list[] = array(
      'icon'    => 'note',
      'title'   => _txt('ct.vo_members.pl'),
      'url'     => array(
        'controller' => 'vo_members',
        'action'     => 'index'
      )
    );

    return $action_list;
  }

  /**
   * @param Integer $coId
   * @return array|null
   */

  public function getConfiguration($coId)
  {

    // Get all the config data. Even the EOFs that i have now deleted
    $args = array();
    $args['conditions']['VoMember.co_id'] = $coId;
    $args['contain'] = false;

    $data = $this->find('first', $args);
    // There is no configuration available for the plugin. Abort
    if(empty($data)) {
      return null;
    }

    Configure::write('Security.useOpenSsl', true);
    $data["VoMember"]["authkey_ops"] = Security::decrypt(base64_decode($data["VoMember"]["authkey_ops"]), Configure::read('Security.salt'));
    return $data;
  }

}