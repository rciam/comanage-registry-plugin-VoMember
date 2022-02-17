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
      $this->VomsMember->opsConnect($vo_member_config['VoMember']);
      // Request the data
      $response = $this->VomsMember->opsRequest();
      // Parse data and create insert values
      $insert_values = $this->VomsMember->parseOpsResponse($response->result);
      // Create the temp table and insert the new values
      $this->VomsMember->createTempVoms();


    } catch (Exception $e) {
      $this->out($e->getMessage());
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

}