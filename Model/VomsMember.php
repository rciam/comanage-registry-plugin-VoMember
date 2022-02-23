<?php

class VomsMember extends AppModel {
  public $name = "VomsMember";

  // Default display field for cake generated views
  public $displayField = 'subject';

  // Cache the Http connection and server configuration
  protected $Http = null;
  protected $baseUrl = null;
  protected $pathURL = null;
  protected $authKey = null;

  // TODO: Since the data only change from Source i will cache them and invalidate the cache only
  //       if the saveAll succeeds

  public $virtualFields = array(
    'certificate' => "string_agg(VomsMember.subject || '"
                    . VoMembersDelimitersEnum::ValueSeparate
                    . "' || VomsMember.issuer, '"
                    . VoMembersDelimitersEnum::LineSeperate
                    . "')",
  );

  public $cert_virtualFields = array(
    'person'      => "string_agg(CoPerson.id || '"
                     . VoMembersDelimitersEnum::ValueSeparate
                     . "' || (Name.given || ' ' || Name.family), '"
                     . VoMembersDelimitersEnum::LineSeperate
                     . "')",
    'roles'       => "string_agg(CoPersonRole.id || '"
                     . VoMembersDelimitersEnum::ValueSeparate
                     . "' || Cou.name, '"
                     . VoMembersDelimitersEnum::LineSeperate
                     . "')"
  );

  private $mapper = array(
    'USERVO' => 'username',
    'EMAIL'  => 'email',
    'CERTDN' => 'subject',
    'CA'     => 'issuer',
    'VO'     => 'vo_id',
    'fqans'  => 'fqans',
    'FIRST_UPDATE' => 'first_update',
    'LAST_UPDATE'  => 'last_update'
  );

  // Validation rules for table elements
  public $validate = array(
    'vo_id' => array(
      'rule' => '/.*/',
      'required' => true,
      'message' => 'A VO ID must be provided',
    ),
    'subject' => array(
      'content' => array(
        'rule' => array('maxLength', 512),
        'required' => false,
        'allowEmpty' => true,
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
    'email' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'username' => array(
      'rule' => '/.*/',
      'required' => false,
      'allowEmpty' => true
    ),
    'fqan' => array(
      'rule' => '/.*/', // TODO: Ideally we should be checking for JSON data
      'required' => false,
      'allowEmpty' => true
    ),
    'first_update' => array(
      'content' => array(
        'rule' => array('validateTimestamp'),
        'required' => false,
        'allowEmpty' => true
      )
    ),
    'last_update' => array(
      'content' => array(
        'rule' => array('validateTimestamp'),
        'required' => false,
        'allowEmpty' => true
      )
    )
  );

  /**
   * Get All CO Certificates linked to
   *
   * @param $co_id
   * @return mixed
   */
  public function getCertMapToPersonRole($co_id) {
    $oargs = array();
    $oargs['joins'][0]['table'] = 'co_org_identity_links';
    $oargs['joins'][0]['alias'] = 'CoOrgIdentityLink';
    $oargs['joins'][0]['type'] = 'INNER';
    $oargs['joins'][0]['conditions'][0] = 'Cert.org_identity_id = CoOrgIdentityLink.org_identity_id';
    $oargs['joins'][1]['table'] = 'co_people';
    $oargs['joins'][1]['alias'] = 'CoPerson';
    $oargs['joins'][1]['type'] = 'INNER';
    $oargs['joins'][1]['conditions'][0] = 'CoOrgIdentityLink.co_person_id = CoPerson.id';
    $oargs['joins'][1]['conditions'][1]['CoPerson.co_id'] = $co_id;
    $oargs['joins'][2]['table'] = 'names';
    $oargs['joins'][2]['alias'] = 'Name';
    $oargs['joins'][2]['type'] = 'INNER';
    $oargs['joins'][2]['conditions'][0] = 'Name.co_person_id = CoPerson.id';
    $oargs['joins'][2]['conditions'][1]['Name.primary_name'] = true;
    $oargs['joins'][3]['table'] = 'co_person_roles';
    $oargs['joins'][3]['alias'] = 'CoPersonRole';
    $oargs['joins'][3]['type'] = 'LEFT';
    $oargs['joins'][3]['conditions'][0] = 'CoPersonRole.co_person_id = CoPerson.id';
    $oargs['joins'][3]['conditions'][1]['CoPersonRole.status'] = array(StatusEnum::Active, StatusEnum::GracePeriod);
    $oargs['joins'][4]['table'] = 'cous';
    $oargs['joins'][4]['alias'] = 'Cou';
    $oargs['joins'][4]['type'] = 'LEFT';
    $oargs['joins'][4]['conditions'][0] = 'CoPersonRole.cou_id = Cou.id';
    $oargs['joins'][4]['conditions'][1] = 'CoPersonRole.cou_id is not null';
    $oargs['group'] = 'Cert.subject';
    $oargs['fields'] = array(
      'Cert.subject',
      'Cert.person',
      'Cert.roles',
    );
    $oargs['contain'] = false;

    $this->Cert = ClassRegistry::init('Cert');
    $this->Cert->virtualFields = array_merge_recursive($this->Cert->virtualFields, $this->cert_virtualFields);
    $mapped_certs = $this->Cert->find('all', $oargs);

    // Construct the final array
    $subject_list = array();
    foreach($mapped_certs as $cert_data) {
      $subject_list[ $cert_data['Cert']['subject'] ] = array();
      // Person entries
      $person_entries = explode(VoMembersDelimitersEnum::LineSeperate, $cert_data['Cert']['person']);
      $person_entries = array_unique($person_entries);
      foreach($person_entries as $idx => $pdata) {
        list($subject_list[ $cert_data['Cert']['subject'] ]['person'][$idx]['id'], $subject_list[ $cert_data['Cert']['subject'] ]['person'][$idx]['primary']) = explode(VoMembersDelimitersEnum::ValueSeparate, $pdata);
      }
      if(!is_null($cert_data['Cert']['roles'])) {
        // Role entries
        $role_entries = explode(VoMembersDelimitersEnum::LineSeperate, $cert_data['Cert']['roles']);
        $role_entries = array_unique($role_entries);
        foreach($role_entries as $idx => $rdata) {
          list($subject_list[ $cert_data['Cert']['subject'] ]['roles'][$idx]['id'], $subject_list[ $cert_data['Cert']['subject'] ]['roles'][$idx]['name']) = explode(VoMembersDelimitersEnum::ValueSeparate, $rdata);
        }
      }
    }
    return $subject_list;
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
   * Obtain all COUs within a specified CO.
   *
   * @param  integer CO ID
   * @param  string Format, one of "names", "ids", or "hash" of id => name
   * @return Array List or hash of member COUs, as specified by $format
   */

  public function allCous($coId, $format="hash") {
    $args['conditions']['Cou.co_id'] = $coId;
    $args['order'] = 'Cou.name ASC';
    $args['contain'] = false;

    $this->Cou = ClassRegistry::init('Cou');
    $cous = $this->Cou->find("list", $args);

    if($cous) {
      switch($format) {
        case 'names':
          return(array_values($cous));
          break;
        case 'ids':
          return(array_keys($cous));
          break;
        default:
          return($cous);
          break;
      }
    }

    return(array());
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

  /**
   * @param string  $config Plugin configuration
   */
  public function opsConnect($config) {
    if(!$config['authkey_ops']) {
      throw new InvalidArgumentException(_txt('er.vo_members.notfound', array('Authentication Key')));
    }

    if(!$config['base_url']) {
      throw new InvalidArgumentException(_txt('er.vo_members.notfound', array('Base URL')));
    }

    if(!$config['endpoint_ops']) {
      throw new InvalidArgumentException(_txt('er.vo_members.notfound', array('Path URL')));
    }

    $this->baseUrl = $config['base_url'];
    $this->pathURL = $config['endpoint_ops'];
    $this->authKey = $config['authkey_ops'];

    $this->Http = new HttpSocket(array(
                                   'ssl_verify_host' => version_compare(PHP_VERSION, '5.6.0', '>=')
                                 ));
  }

  /**
   * @param array $data    Data for POST method
   * @param string $action HTTP Request Protocol
   * @return mixed
   */
  public function opsRequest($data=array(), $action="get") {
    $options = array(
      'header' => array(
        'Accept'        => 'application/json',
        'X-API-Key'     =>  $this->authKey
      )
    );

    $results = $this->Http->$action(
      $this->baseUrl . $this->pathURL,
      ($action == 'get' ? $data : json_encode($data)),
      $options);

    if($results->code != 200) {
      // This is probably an RDF blob, which is slightly annoying to parse.
      // Rather than do it properly since we don't parse RDF anywhere else,
      // we return a generic error.
      throw new RuntimeException(_txt('er.vo_members.http.failed', array($results->code)));
    }

    return json_decode($results->body, true);
  }


  /**
   * Parse the data into what my database expects to save
   *
   * @param $data     JSON Decoded data
   * @return array    Create list of values ready for saveMany method
   */
  public function parseOpsResponse($data) {
      if(empty($data)) {
        return array();
      }

      $values = array();
      foreach ($data as $idx => $items) {
        foreach ($items['row'] as $item) {
          $column = key($item);
          if($column == 'fqans') {
            $values[$idx][ $this->mapper[$column] ] = serialize(json_encode($item[$column][0])) ?? '';
          } else {
            $values[$idx][ $this->mapper[$column] ] = $item[$column][0] ?? '';
          }
        }
      }

      return $values;
  }

  /**
   * Update the table with the existing data
   *
   * @param array $data
   * @return void
   */
  public function processData($data) {
    if(empty($data)) {
      return;
    }


//    // The users view is no longer required.
    // XXX Each time i pull the data from the operations portal all of them will be udpated because
    //     at least the column last_updated will have a new value. Perhaps the best approach is to
    // Create temp table does not inherit indexes and still we need to copy. Perhaps the best approach is
    // to clone the table, delete the old one and then rename the temp table
    $db = ConnectionManager::getDataSource('default');

    if(isset($db->config['prefix'])) {
      $prefix = $db->config['prefix'];
    }

    // Create table as cm_voms_members only schema
    $table = ($prefix ?? '') . Inflector::tableize($this->name);
    try {
      $this->VomsMemberClone = ClassRegistry::init('VoMember.VomsMemberClone');
      $this->VomsMemberClone->tblCreate($table);
      // save the data
      $this->VomsMemberClone->importData($data);

      $db->begin();
      // Drop the current table
      $this->query('DROP TABLE IF EXISTS ' . $table . ' CASCADE');
      // Rename the tmp table
      $this->VomsMemberClone->tableRename($table);
    }
    catch(Exception $e) {
      $err = filter_var($e->getMessage(),FILTER_SANITIZE_SPECIAL_CHARS);
      $this->log(__METHOD__ . "::error message => " . $err, LOG_DEBUG);
      $db->rollback();
      return;
    }

    $db->cacheSources = true;
    $db->commit();
  }

}