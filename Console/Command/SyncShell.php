<?php
/*
 * For execution run: Console/cake VoMember.syncVo
 * Crontab entry(run everyday at midnight:
   m h  dom mon dow   command
   0 0 * * * /path/to/comanage/app && Console/cake VoMember.syncVo -c 2
 * */
App::uses('HttpSocket', 'Network/Http');


class SyncShell extends AppShell {
  public $uses = array('Co',
                       'VoMember.VomsMember',
                       'VoMember.VoMember',
                       'CoLocalization');

  // Cache the Http connection and server configuration
  protected $Http = null;
  protected $baseUrl = null;
  protected $pathURL = null;
  protected $authKey = null;

  public function main() {
    // Run background / scheduled tasks. For now, we only run expirations so we don't
    // bother with any command line flags. This might need to change in the future,
    // especially if we want to run things on an other than nightly/daily schedule.

    _bootstrap_plugin_txt();

    // Load localizations
    $this->CoLocalization->load($this->params['coid']);

    // Get plugin configuration
    $vo_member_config = $this->VoMember->getConfiguration($this->params['coid']);

    if(empty($vo_member_config)) {
      $this->out("- " . _txt('sh.sync.no.config'));
      return;
    }

    try {
      // Try to setup HTTP connection to Operations Portal
      $this->opsConnect($vo_member_config['VoMember']);
      // Request the data
      $parsed_data = $this->opsRequest();

      // Currently we only support pull
      // XXX This is an implementation ot OPS API V1
      $this->pull_V1($vo_member_config['VoMember']);
    } catch (Exception $e) {
      $this->out("- " . _txt('sh.sync.no.config'));
    }

  }

  /**
   * @return ConsoleOptionParser
   */
  public function getOptionParser() {
    $parser = parent::getOptionParser();

    $parser->addOption(
      'coid',
      array(
        'short' => 'c',
        'help' => _txt('sh.sync.arg.coid'),
        'boolean' => false,
        'default' => false
      )
    )->epilog(_txt('sh.job.arg.epilog'));

    return $parser;
  }

  /**
   * @param array $config  Plugin configuration
   * @return void
   */
  protected function opsConnect($config) {
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
  protected function opsRequest($data=array(), $action="get") {
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

    return json_decode($results->body);
  }

}