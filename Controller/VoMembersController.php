<?php
App::uses('StandardController', 'Controller');

class VoMembersController extends StandardController {
  // Class name, used by Cake
  public $name = 'VoMembers';

  /**
   * Edit Rciam Stats Viewer Settings
   *
   * @param integer $id
   * @return void
   */
  public function edit($id=null) {
    //Get data if any for the configuration of VoMember
    $configData = $this->VoMember->getConfiguration($this->cur_co['Co']['id']);
    $id = isset($configData['VoMember']) ? $configData['VoMember']['id'] : -1;

    if($this->request->is('post')) {
      // We're processing an update
      // if i had already set edit before, now retrieve the entry and update
      if($id > 0){
        $this->VoMember->id = $id;
        $this->request->data['VoMember']['id'] = $id;
      }

      try {
        $save_options = array(
          'validate'  => true,
        );

        if($this->VoMember->save($this->request->data, $save_options)){
          $this->Flash->set(_txt('rs.saved'), array('key' => 'success'));
        } else {
          $invalidFields = $this->VoMember->invalidFields();
          $this->log(__METHOD__ . '::exception error => ' . print_r($invalidFields, true), LOG_DEBUG);
          $this->Flash->set(_txt('er.vo_members.db.failed'), array('key' => 'error'));
        }
      }
      catch(Exception $e) {
        $this->log(__METHOD__ . '::exception error => ' .$e, LOG_DEBUG);
        $this->Flash->set($e->getMessage(), array('key' => 'error'));
      }
      // Redirect back to a GET
      $this->redirect(array('action' => 'edit', 'co' => $this->cur_co['Co']['id']));
    } else {
      // Return the existing data if any
      $this->set('vv_vo_members', $configData);
    }
  }

  /**
   * Callback before other controller methods are invoked or views are rendered.
   *
   * @since  VomsMember v1.0
   */
  public function beforeFilter() {
    $this->Security->blackHoleCallback = 'reloadConfig';
    // Since we're overriding, we need to call the parent to run the authz check
    parent::beforeFilter();
  }

  /**
   * Reload if our Session is no longer valid
   */

  public function reloadConfig() {
    $this->Flash->set(_txt('er.vo_members.blackhauled'), array('key' => 'information'));
    return $this->redirect("/");
  }


  /**
   * For Models that accept a CO ID, find the provided CO ID.
   * - precondition: A coid must be provided in $this->request (params or data)
   *
   * @since  COmanage Registry v3.1.x
   * @return Integer The CO ID if found, or -1 if not
   */

  public function parseCOID($data = null) {
    if($this->action == 'edit') {
      if(isset($this->request->params['named']['co'])) {
        return $this->request->params['named']['co'];
      }
    }

    return parent::parseCOID();
  }

  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for auth decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v3.1.x
   * @return Array Permissions
   */

  function isAuthorized() {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $roles = $this->Role->calculateCMRoles();

    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();

    // Determine what operations this user can perform
    $p['edit'] = ($roles['cmadmin'] || $roles['coadmin']);

    $this->set('vv_permissions', $p);

    return($p[$this->action]);
  }
}