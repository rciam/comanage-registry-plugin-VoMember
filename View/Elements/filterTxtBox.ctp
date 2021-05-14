<?php

?>
<div id="<?php print $vv_id; ?>" class="filter-textbox hidden" onclick="event.stopPropagation();">
  <label id="filter-icon" for="settings-filter-input">
    <i class="fa fa-filter default-cursor" aria-hidden="true"></i>
    <span class="sr-only">Filter</span>
  </label>
  <label id="x-icon" onclick="filter_reset(this);">
    <i class="fa fa-times-circle fa-icon-color" aria-hidden="true"></i>
    <span class="sr-only">Clear</span>
  </label>
  <input id="cert-filter<?php print $vv_id; ?>" class="cert-filter form-control" type="text" placeholder="Filter...">
</div>
