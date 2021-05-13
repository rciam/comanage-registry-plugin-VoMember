<?php

class VomsMember extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = 'other';

  // Default display field for cake generated views
  public $displayField = 'subject';

  public $virtualFields = array(
    'certificate' => "string_agg(VomsMember.subject || '"
                    . VomsMembersDelimitersEnum::DNsSeparate
                    . "' || VomsMember.issuer, '"
                    . VomsMembersDelimitersEnum::CertSeparate
                    . "')",
  );


  // Validation rules for table elements
  public $validate = array(
    'vo_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'message' => 'A VO ID must be provided',
    ),
    'subject' => array(
      'content' => array(
        'rule' => array('maxLength', 512),
        'required' => false,
        'allowEmpty' => false,
        'message' => 'Please enter a valid cert subject DN',
      ),
      'filter' => array(
        'rule' => array('validateInput'),
      ),
    ),
    'issuer' => array(
      'content' => array(
        'rule' => array('maxLength', 512),
        'required' => false,
        'allowEmpty' => true,
        'message' => 'Please enter a valid cert issuer DN',
      ),
      'filter' => array(
        'rule' => array('validateInput'),
      ),
    ),
  );

  /**
   * Expose menu items.
   *
   * @ since COmanage Registry v3.1.x
   * @ return Array with menu location type as key and array of labels, controllers, actions as values.
   */

  public function cmPluginMenus()
  {
    $this->log(__METHOD__ . '::@', LOG_DEBUG);
    $menu_items = array();
    $menu_items['cogroups'][_txt('ct.voms_members.pl')] = array(
      'controller' => "voms_members",
      'action' => 'index',
    );
    return $menu_items;
  }

  /**
   * Get the entire list of VOMS
   *
   * @param [string]   List of Subject DNs
   * @return array List of VOMS names
   */
  public function getAllVomsIDs($subject=false) {
    $args = array();
    $args['conditions'][] = 'VomsMember.vo_id IS NOT NULL';
    if($subject !== false) {
      $args['conditions']['VomsMember.subject'] = $subject;
    }
    $args['fields'] = 'DISTINCT VomsMember.vo_id';
    $args['contain'] = false;

    $entries = $this->find('all', $args);

    return (empty($entries)) ? array() : $entries;
  }

  /**
   * Get all the Certificates linked under all OrgIdentities of the user
   *
   * @param string  $username     User's username stored in the Session
   * @param integer $co_id        CO Id
   * @return array|array[]|false  List of Subject DNs|false if no CO Person linked to the OrgIdentity
   * @since VomsMember v1.0.0
   */
  public function getCertificates($username, $co_id) {
    // We use $oargs here instead of $args because we may reuse this below
    $args = array();
    $args['joins'][0]['table'] = 'identifiers';
    $args['joins'][0]['alias'] = 'Identifier';
    $args['joins'][0]['type'] = 'INNER';
    $args['joins'][0]['conditions'][0] = 'OrgIdentity.id=Identifier.org_identity_id';
    $args['conditions']['Identifier.identifier'] = $username;
    $args['conditions']['Identifier.login'] = true;
    // Join on identifiers that aren't deleted (including if they have no status)
    $args['conditions']['OR'][] = 'Identifier.status IS NULL';
    $args['conditions']['OR'][]['Identifier.status <>'] = SuspendableStatusEnum::Suspended;
    // As of v2.0.0, OrgIdentities have validity dates, so only accept valid dates (if specified)
    // Through the magic of containable behaviors, we can get all the associated
    $args['conditions']['AND'][] = array(
      'OR' => array(
        'OrgIdentity.valid_from IS NULL',
        'OrgIdentity.valid_from < ' => date('Y-m-d H:i:s', time()),
      )
    );
    $args['conditions']['AND'][] = array(
      'OR' => array(
        'OrgIdentity.valid_through IS NULL',
        'OrgIdentity.valid_through > ' => date('Y-m-d H:i:s', time()),
      )
    );
    $args['conditions']['OrgIdentity.co_id'] = $co_id;
    // data we need in one clever find
    $args['contain'][] = 'PrimaryName';
    $args['contain'][] = 'Identifier';
    $args['contain']['CoOrgIdentityLink'] = 'CoPerson';

    $this->OrgIdentity = ClassRegistry::init('OrgIdentity');
    $orgIdentity = $this->OrgIdentity->find('all', $args);
    // Extract all the OrgIdentity IDs
    if(empty($orgIdentity[0]["CoOrgIdentityLink"][0]["CoPerson"]["id"])) {
      return false;
    }

    // I am filtering using CO Id, so i should only get back one OrgIdentity
    $co_person = $orgIdentity[0]["CoOrgIdentityLink"][0]["CoPerson"]["id"];

    // Get all CO Person's OrgIdentities and extract their linked Certificates
    $oargs = array();
    $oargs['joins'][0]['table'] = 'co_org_identity_links';
    $oargs['joins'][0]['alias'] = 'CoOrgIdentityLink';
    $oargs['joins'][0]['type'] = 'INNER';
    $oargs['joins'][0]['conditions'][0] = 'OrgIdentity.id=CoOrgIdentityLink.org_identity_id';
    $oargs['conditions']['CoOrgIdentityLink.co_person_id'] = $co_person;
    // As of v2.0.0, OrgIdentities have validity dates, so only accept valid dates (if specified)
    // Through the magic of containable behaviors, we can get all the associated
    $oargs['conditions']['AND'][] = array(
      'OR' => array(
        'OrgIdentity.valid_from IS NULL',
        'OrgIdentity.valid_from < ' => date('Y-m-d H:i:s')
      )
    );
    $oargs['conditions']['AND'][] = array(
      'OR' => array(
        'OrgIdentity.valid_through IS NULL',
        'OrgIdentity.valid_through > ' => date('Y-m-d H:i:s')
      )
    );
    // data we need in one clever find
    $oargs['contain']['CoOrgIdentityLink']['OrgIdentity'] = 'Cert';

    $orgIdentities = $this->OrgIdentity->find('all', $oargs);

    // Extract an array with the User's Subject DNs
    $subject_dns = Hash::extract($orgIdentities, '{n}.Cert.{n}.subject');
    return $subject_dns;
  }
}