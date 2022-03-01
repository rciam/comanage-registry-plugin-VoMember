<?php
$fqans = array();
if(!empty($vv_vo_members[0]['VomsMember']['fqans'])) {
  $fqans = json_decode(unserialize($vv_vo_members[0]['VomsMember']['fqans']), true);
}

$params = array('title' => $title_for_layout);
print $this->element("pageTitle", $params);

?>

<ul id="<?php
print $this->action; ?>_name" class="fields form-list">
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.subdn') . ':' . '</b>';
        ?>
      </span>
    <span class="field-info">
        <?php
        print filter_var(($vv_vo_members[0]['VomsMember']['subject'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
        ?>
       </span>
  </li>
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.isdn') . ':' . '</b>';
        ?>
      </span>
    <span class="field-info">
        <?php
        print filter_var(($vv_vo_members[0]['VomsMember']['issuer'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
      </span>
  </li>
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.vo_id') . ':' . '</b>';
        ?>
      </span>
    <span class="field-info">
        <?php
        print filter_var(($vv_vo_members[0]['VomsMember']['vo_id'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
      </span>
  </li>
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.username') . ':' . '</b>';
        ?>
      </span>
    <span class="field-info">
        <?php
        print filter_var(($vv_vo_members[0]['VomsMember']['username'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
      </span>
  </li>
  <li>
    <span class="field-name">
      <?php print '<b>' . _txt('pl.vo_members.email') . ':' . '</b>';  ?>
    </span>
    <span class="field-info">
        <?php print filter_var(($vv_vo_members[0]['VomsMember']['email'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
    </span>
  </li>
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.first_update') . ':' . '</b>';
        ?>
      </span>
    <span class="field-info">
        <?php
        print filter_var(($vv_vo_members[0]['VomsMember']['first_update'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
      </span>
  </li>
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.last_update') . ':' . '</b>';
        ?>
      </span>
    <span class="field-info">
        <?php
        print filter_var(($vv_vo_members[0]['VomsMember']['last_update'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
      </span>
  </li>
  <?php
  $idx = 1;
  foreach($fqans as $item): ?>
  <li>
      <span class="field-name">
        <?php
        print '<b>' . _txt('pl.vo_members.fqan', array($idx)) . ':' . '</b>';
        $idx++;
        ?>
  </span>
  <span class="field-info">
        <?php
        print filter_var(($item[0] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS); ?>
      </span>
  </li>
  <?php endforeach; ?>
</ul>
