<?php

class VoMember extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = 'other';

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
      'controller' => "vo_members",
      'action' => 'index',
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


}