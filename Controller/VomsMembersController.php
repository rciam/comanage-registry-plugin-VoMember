<?php
App::uses('StandardController', 'Controller');

class VomsMembersController extends StandardController {
  // Class name, used by Cake
  public $name = 'VomsMembers';

  public $uses = array(
    'VomsMember.VomsMember'
  );

  /**
   * Callback after controller methods are invoked but before views are rendered.
   *
   * @since  VomsMember v1.0.0
   */
  public function beforeRender()
  {
    parent::beforeRender();

    $voms_members = isset($this->viewVars["voms_members"]) ? $this->viewVars["voms_members"] : array();
    $voms_list = array();

    if(empty($this->viewVars["voms_members"])) {
      $u = $this->Session->read('Auth.User.username');
      // If i have no username just reload
      if(empty($u)) {
        $this->reloadConfig();
      }
      // Fetch all the Subject DNs the user has
      $subject_dns = $this->VomsMember->getCertificates($u, $this->cur_co["Co"]["id"]);
      // Fetch all Entries from VomsMembers having the subject DNs the user has
      $voms_members = $this->VomsMember->getVomsMemberships($subject_dns);

      if(!empty($voms_members)) {
        // Organize $voms_members by vo_id
        $voms_list = Hash::combine(
          $voms_members,
          '{n}.VomsMember.subject',
          '{n}.VomsMember',
          '{n}.VomsMember.vo_id',
      );
      }
    } else {
      if(!empty($voms_members)) {
        foreach($voms_members as $voms) {
          $certificates = explode(VomsMembersDelimitersEnum::CertSeparate, $voms["VomsMember"]["certificate"]);
          foreach($certificates as $cert) {
            list($subject, $issuer) = explode(VomsMembersDelimitersEnum::DNsSeparate, $cert);
            $voms_list[$voms["VomsMember"]["vo_id"]][] = array(
              'subject' => $subject,
              'issuer' => $issuer,
            );
          }
        }
      }
    }
    // Get all VOMS
    $all_voms = $this->VomsMember->getAllVomsIDs();
    if(!empty($all_voms)) {
      $this->set('vv_voms_list_name', Hash::extract($all_voms, '{n}.VomsMember.vo_id'));
    }

    $this->set('vv_voms_list', $voms_list);
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
    $this->log(__METHOD__ . '::location => ' . print_r($location, true), LOG_DEBUG);
    $this->Flash->set(_txt('er.voms_members.blackhauled'), array('key' => 'information'));
    return $this->redirect("/");
  }

  /**
   * Determine the conditions for pagination of the index view, when rendered via the UI.
   *
   * @since  COmanage Registry v4.0.0
   * @return Array An array suitable for use in $this->paginate
   */

  public function paginationConditions() {
    $u = $this->Session->read('Auth.User.username');
    // If i have no username just reload
    if(empty($u)) {
      $this->reloadConfig();
    }
    // Fetch all the Subject DNs the user has
    $subject_dns = $this->VomsMember->getCertificates($u, $this->cur_co["Co"]["id"]);
    $ret = array();
    $ret['conditions']['VomsMember.subject'] = $subject_dns;
    $ret['group'] = 'VomsMember.vo_id';
    $ret['fields'] = array(
      'VomsMember.vo_id',
      'VomsMember.certificate'
    );
    $ret['contain'] = false;

    return $ret;
  }



  /**
   * For Models that accept a CO ID, find the provided CO ID.
   * - precondition: A coid must be provided in $this->request (params or data)
   *
   * @since  COmanage Registry v3.1.x
   * @return Integer The CO ID if found, or -1 if not
   */

  public function parseCOID($data = null) {
    if($this->action == 'index') {
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
    $p['index'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['user']);

    $this->set('vv_permissions', $p);

    return($p[$this->action]);
  }
}