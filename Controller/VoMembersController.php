<?php
App::uses('StandardController', 'Controller');

class VoMembersController extends StandardController {
  // Class name, used by Cake
  public $name = 'VoMembers';

  public $uses = array(
    'VoMember.VomsMember'
  );

  // Establish pagination parameters for HTML views
  public $paginate = array(
    'limit' => 25,
    'order' => array(
      'VomsMember.vo_id' => 'asc'
    )
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
    $voms_list_names = array();

    foreach($voms_members as $voms) {
      $certificates = explode(VomsMembersDelimitersEnum::LineSeperate, $voms["VomsMember"]["certificate"]);
      foreach($certificates as $cert) {
        list($subject, $issuer) = explode(VomsMembersDelimitersEnum::ValueSeparate, $cert);
        $voms_list[$voms["VomsMember"]["vo_id"]][] = array(
          'subject' => $subject,
          'issuer' => $issuer,
        );
      }
    }

    // Fetch VOMs names for the drop down list
    // Get all VOMS
    if($this->viewVars["vv_permissions"]["all"]
       && $this->viewVars["vv_permissions"]["all"]) {
      $all_voms = $this->VomsMember->getAllVomsIDs();
      if(!empty($all_voms)) {
        $all_voms_list = Hash::extract($all_voms, '{n}.VomsMember.vo_id');
        sort($all_voms_list);
        $this->set('vv_voms_list_name', $all_voms_list);
      }
    } elseif(isset($this->viewVars["vv_subject_dns"])){
      $voms_members = $this->VomsMember->getAllVomsIDs($this->viewVars["vv_subject_dns"]);
      $my_voms_list = Hash::extract($voms_members, '{n}.VomsMember.vo_id');
      sort($my_voms_list);
      $this->set('vv_voms_list_name', $my_voms_list);
    } else {
      $this->set('vv_voms_list_name', false);
    }

    $this->set('vv_voms_list', $voms_list);

    // COU list
    $cou_list = $this->VomsMember->allCous($this->cur_co["Co"]["id"],'names');
    $this->set('vv_cous', (!empty($cou_list)) ? array_combine($cou_list, $cou_list) : array() );

    // Mapped Cert list
    $cert_list = $this->VomsMember->getCertMapToPersonRole($this->cur_co["Co"]["id"]);
    $this->set('vv_cert_mlist', $cert_list);
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
    $this->Flash->set(_txt('er.vo_members.blackhauled'), array('key' => 'information'));
    return $this->redirect("/");
  }

  /**
   * Insert search parameters into URL for index.
   * - postcondition: Redirect generated
   *
   * @since  VomsMember v1.0.0
   */

  public function search() {
    $url['action'] = $this->request->data['Action']['name'];
    $url['controller'] = 'vo_members';

    // build a URL will all the search elements in it
    // the resulting URL will be
    foreach($this->data['search'] as $field => $value){
      if(!empty($value)) {
        $url['search.'.$field] = urlParamEncode($value);
      }
    }

    // XXX Put these two always last
    $url['co'] = $this->cur_co['Co']['id'];
    if(isset($this->request->data["VomsMember"]["all"])) {
      $url['all'] = ($this->request->data["VomsMember"]["all"]) ? true : false;
    }
    // redirect the user to the url
    $this->redirect($url, null, true);
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
    $ret = array();

    // Subject DN
    $req_subjectdn = isset($this->request->params['named']['search.subject']) ? urlParamDecode($this->request->params['named']['search.subject']) : "";
    // VO Name
    if(isset($this->request->params['named']['search.void'])) {
      $ret['conditions']['VomsMember.vo_id'] = urlParamDecode($this->request->params['named']['search.void']);
    }
    // Issuer DN
    if(isset($this->request->params['named']['search.issuer'])) {
      $issuer = urlParamDecode($this->request->params['named']['search.issuer']);
      $ret['conditions']['VomsMember.issuer iLIKE'] = "%{$issuer}%";
    }

    if(isset($this->request->params["named"]["all"])
       && $this->request->params["named"]["all"]
       && $this->viewVars["vv_permissions"]["all"]) {
      $ret['conditions'][] = empty($req_subjectdn)
        ? "VomsMember.subject IS NOT NULL"
        : "VomsMember.subject iLIKE '%{$req_subjectdn}%'";
      $this->set('vv_all', true);
    } else {
      if(empty($req_subjectdn)) {
        $subject_dns = $this->VomsMember->getCertificates($u, $this->cur_co["Co"]["id"]);
        $ret['conditions']['VomsMember.subject'] = $subject_dns;
      } else {
        $ret['conditions']['VomsMember.subject iLIKE'] = "%{$req_subjectdn}%";
      }
      $this->set('vv_subject_dns', $subject_dns);
      $this->set('vv_all', false);
    }

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
    $p['search'] = ($roles['cmadmin'] || $roles['coadmin'] || $roles['couadmin'] || $roles['user']);
    $p['all'] = ($roles['cmadmin'] || $roles['coadmin']);

    $this->set('vv_permissions', $p);

    return($p[$this->action]);
  }
}